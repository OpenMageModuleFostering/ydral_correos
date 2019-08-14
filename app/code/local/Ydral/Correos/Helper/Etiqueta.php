<?php 

/**
 *
 */
class Ydral_Correos_Helper_Etiqueta extends Mage_Core_Helper_Abstract
{

    public function getXmlEtiqueta(array $data)
    {
        
        if (empty($data)) return false;
        
        $xmlSend = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://www.correos.es/iris6/services/preregistroetiquetas">
<soapenv:Header/>
<soapenv:Body>
<SolicitudEtiqueta>
<fechaOperacion>{$data['fechaOperacion']}</fechaOperacion>
{$data['dataAuth']}
<CodEnvio>{$data['codEnvio']}</CodEnvio>
<Care>000000</Care>
<ModDevEtiqueta>2</ModDevEtiqueta>
</SolicitudEtiqueta>
</soapenv:Body>
</soapenv:Envelope>
XML;

        return $xmlSend;
        
    }

}