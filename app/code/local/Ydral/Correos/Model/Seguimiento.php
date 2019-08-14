<?php 

/**
 *
 */
class Ydral_Correos_Model_Seguimiento extends Mage_Core_Model_Abstract
{   

    /**
    *
    */
    public function localizarEnvioFases ($codEnvio)
    {
        
        $xml = '<?xml version="1.0" encoding="utf-8" ?><ConsultaXMLin Idioma="1" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><Consulta><Codigo>'.$codEnvio.'</Codigo></Consulta></ConsultaXMLin>';


        try
        {
        
            /**
             *  xml
             */
            $xmlSend = Mage::helper('correos/seguimiento')->getXmlSeguimientoFases($xml);
            $peticionCorreos = $this->sendRequestCorreos($xmlSend, 'ServiciosWebLocalizacionMI/ConsultaLocalizacionEnviosFases');
            if ($peticionCorreos == '')
            {
                Mage::throwException(
                    Mage::helper('sales')->__('No se ha podido contacto con el servidor de Correos. Respuesta vac&iacute;a.')
                );
                return false;
            }
            
            
            /**
             *
             */
            $dataXml = @simplexml_load_string($peticionCorreos);
            if (!$dataXml->registerXPathNamespace('respuesta', 'ServiciosWebLocalizacionMI/'))
            {
                //  ha devuelto un error;
                Mage::throwException(
                    Mage::helper('correos')->__('Error en los datos devueltos por Correos.')
                );
                return false;
            }
            $_respuesta = $dataXml->xpath('//respuesta:ConsultaLocalizacionEnviosFasesResult');
            $_respuestaXml = @simplexml_load_string($_respuesta[0]);
            $respuesta = $_respuestaXml->xpath('//Respuestas/DatosIdiomas/DatosEnvios/Datos');

            $_estado = (string) $respuesta[0]->Estado;
            $_fecha = (string) $respuesta[0]->Fecha;
            
        } catch (Exception $e) {            
            if (!$e->getMessage())
            {
                Mage::throwException(
                    Mage::helper('correos')->__('Error en los datos devueltos por Correos. Problema de conexión o datos.')
                );
            } else {
                Mage::throwException($e->getMessage());
            }
        }
        
        return $respuesta;
        
    }
    
    /**
     *
     */
    public function getLastStateEnvioFases($codEnvio)
    {
        $_trackCorreos = $this->localizarEnvioFases($codEnvio);
        if ($_trackCorreos)
        {
            $track = end($_trackCorreos);
            $_estado = (string) $track->Estado;
            $_fecha = (string) $track->Fecha;
            return utf8_decode($_estado . " [" . $_fecha . "]");    
        }
        return Mage::helper('correos')->__('Sin datos');
    }
    
    /**
     *
     */
    protected function sendRequestCorreos($xmlSend, $tipoSolicitud = '')
    {
        $urlPeticion = Mage::getStoreConfig('correos/general/gateway_localizacion');
        Mage::helper('correos')->_logger('Peticion seguimiento (' . $tipoSolicitud . '): ' . $xmlSend);
        try
        {
            $xmlResponse = Mage::getModel('correos/curl')->callCurl('', $urlPeticion, $xmlSend, $tipoSolicitud);
        } catch (Exception $e) {
            $xmlResponse = '';
            
        }        
        return $xmlResponse;        
    }
    
}