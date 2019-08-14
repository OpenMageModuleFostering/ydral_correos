<?php 

/**
 *
 */
class Ydral_Correos_Helper_Seguimiento extends Mage_Core_Helper_Abstract
{

    /**
     *
     */
    public function getXmlSeguimientoMasivo($xml)
    {
        
        if (empty($xml)) return false;
        
        $xmlSend = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<soap:Body>
<ConsultaLocalizacionEnviosFasesMasivo xmlns="ServiciosWebLocalizacionMI/">
<XMLin><![CDATA[{$xml}]]></XMLin>
</ConsultaLocalizacionEnviosFasesMasivo>
</soap:Body>
</soap:Envelope>
XML;

        return $xmlSend;
        
    }
    
    
    /**
     *
     */
    public function getXmlSeguimientoFases($xml)
    {
        
        if (empty($xml)) return false;
        
        $xmlSend = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<soap:Body>
<ConsultaLocalizacionEnviosFases xmlns="ServiciosWebLocalizacionMI/">
<XMLin><![CDATA[{$xml}]]></XMLin>
</ConsultaLocalizacionEnviosFases>
</soap:Body>
</soap:Envelope>
XML;

        return $xmlSend;
        
    }
    

}
