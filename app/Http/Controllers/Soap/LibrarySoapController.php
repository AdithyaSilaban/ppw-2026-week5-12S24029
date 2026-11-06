<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LibrarySoapController extends Controller
{
    /**
     * 1. Menyajikan Dokumen Kontrak WSDL (GET /soap/sipus.wsdl)
     */
    public function wsdl(): Response
    {
        $endpointUrl = url('/soap/sipus');

        $wsdlXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="SipusLibrarySoapService"
             targetNamespace="http://sipus.del.ac.id/soap/clearance"
             xmlns:tns="http://sipus.del.ac.id/soap/clearance"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns="http://schemas.xmlsoap.org/wsdl/">

    <!-- Message: Spesifikasi Parameter Input dan Output -->
    <message name="CheckClearanceStatusRequest">
        <part name="nim_peminjam" type="xsd:string"/>
    </message>
    <message name="CheckClearanceStatusResponse">
        <part name="nim_peminjam" type="xsd:string"/>
        <part name="active_loans" type="xsd:int"/>
        <part name="is_cleared" type="xsd:boolean"/>
    </message>

    <!-- PortType: Daftar Operasi yang Disediakan -->
    <portType name="SipusClearancePortType">
        <operation name="CheckClearanceStatus">
            <input message="tns:CheckClearanceStatusRequest"/>
            <output message="tns:CheckClearanceStatusResponse"/>
        </operation>
    </portType>

    <!-- Binding: Mengikat Operasi ke Protokol SOAP over HTTP -->
    <binding name="SipusClearanceSoapBinding" type="tns:SipusClearancePortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="CheckClearanceStatus">
            <soap:operation soapAction="http://sipus.del.ac.id/soap/clearance#CheckClearanceStatus"/>
            <input><soap:body use="literal" namespace="http://sipus.del.ac.id/soap/clearance"/></input>
            <output><soap:body use="literal" namespace="http://sipus.del.ac.id/soap/clearance"/></output>
        </operation>
    </binding>

    <!-- Service: Alamat URL Fisik Endpoint Layanan -->
    <service name="SipusLibrarySoapService">
        <port name="SipusClearancePort" binding="tns:SipusClearanceSoapBinding">
            <soap:address location="{$endpointUrl}"/>
        </port>
    </service>
</definitions>
XML;

        return response($wsdlXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * 2. Memproses Permintaan SOAP Envelope (POST /soap/sipus)
     */
    public function handle(Request $request): Response
    {
        $rawXml = trim($request->getContent());

        if (empty($rawXml)) {
            return $this->soapFault('soap:Client', 'Body XML SOAP tidak boleh kosong.');
        }

        // Parsing XML secara aman
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($rawXml);

        if ($xml === false) {
            return $this->soapFault('soap:Client', 'Format XML tidak valid (Malformed XML).');
        }

        // Ekstraksi nilai tag <nim_peminjam> tanpa terikat prefix namespace
        $nimNodes = $xml->xpath('//*[local-name()="CheckClearanceStatusRequest"]/*[local-name()="nim_peminjam"]')
            ?: $xml->xpath('//*[local-name()="nim_peminjam"]');

        if (empty($nimNodes)) {
            return $this->soapFault(
                'soap:Client',
                'Operasi tidak dikenali atau elemen <nim_peminjam> tidak ditemukan pada CheckClearanceStatusRequest.'
            );
        }

        $nim = trim((string) $nimNodes[0]);
        if ($nim === '') {
            return $this->soapFault('soap:Client', 'Nilai elemen <nim_peminjam> tidak boleh kosong.');
        }

        // Hitung total pinjaman aktif (status = 'dipinjam') dari tabel peminjaman
        $activeLoans = Peminjaman::where('nim_peminjam', $nim)
            ->where('status', 'dipinjam')
            ->count();

        // Mahasiswa bebas pustaka jika tidak ada buku yang sedang dipinjam
        $isCleared = ($activeLoans === 0) ? 'true' : 'false';
        $safeNim   = htmlspecialchars($nim, ENT_XML1, 'UTF-8');

        // Kembalikan amplop resmi SOAP Response
        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Header>
        <m:ProviderInfo>SIPUS-Del Legacy SOAP Clearance Engine v1.0</m:ProviderInfo>
    </soap:Header>
    <soap:Body>
        <m:CheckClearanceStatusResponse>
            <m:nim_peminjam>{$safeNim}</m:nim_peminjam>
            <m:active_loans>{$activeLoans}</m:active_loans>
            <m:is_cleared>{$isCleared}</m:is_cleared>
        </m:CheckClearanceStatusResponse>
    </soap:Body>
</soap:Envelope>
XML;

        return response($responseXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * Helper pembentuk pesan galat standar SOAP Fault (<soap:Fault>)
     */
    private function soapFault(string $faultCode, string $faultMessage): Response
    {
        $safeMsg = htmlspecialchars($faultMessage, ENT_XML1, 'UTF-8');

        $faultXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>{$faultCode}</faultcode>
            <faultstring>{$safeMsg}</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
XML;

        return response($faultXml, 500)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}