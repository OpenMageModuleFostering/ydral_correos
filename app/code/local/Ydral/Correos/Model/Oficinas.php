<?php

/** 
 *
 */
class Ydral_Correos_Model_Oficinas extends Mage_Core_Model_Abstract
{
    
    /**
     *   prepara el selector de oficinas en el checkout
     *
     *  @param int $codpostal
     *  @return array|false
     */
    public function getHtmlOficinas($codpostal)
    {
        return $this->pedirOficinas($codpostal);
    }
    
    /**
    *   llama a correos para comprobar las oficinas relativas al codigo postal
    *
    */
    protected function pedirOficinas($codpostal)
    {
        if (!is_numeric($codpostal)) { return false; }
        if (!Mage::helper('correos')->getValueConfig('active', 'general')) { return false; }

        /**
         *  cadena de peticion
         */
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ejb="http://ejb.mauo.correos.es"><soapenv:Header/><soapenv:Body><ejb:localizadorConsulta><ejb:codigoPostal>'.$codpostal.'</ejb:codigoPostal></ejb:localizadorConsulta></soapenv:Body></soapenv:Envelope>';
        $urlPeticion = Mage::getStoreConfig('correos/general/gateway_recoger');
        Mage::helper('correos')->_logger('Peticion oficina: ' . $xml);

        /**
         *  peticion
         */ 
        try
        {
            $xmlResponse = Mage::getModel('correos/curl')->callCurl('', $urlPeticion, $xml, false);
        } catch (Exception $e) {
            $xmlResponse = '';
            
        }        
        return $this->_parseXmlResponse($xmlResponse);
    }
    
    /**
     *
     * @param mixed $response
     */
    protected function _parseXmlResponse($xmlResponse)
    {
        if ( (strlen(trim($xmlResponse)) > 0) && Mage::helper('correos')->is_valid_xml($xmlResponse) )
        {
            $dataXml = simplexml_load_string($xmlResponse, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");         
            $dataXml->registerXPathNamespace('ns', 'http://ejb.mauo.correos.es');       

            $_tmpData = array();
            $_tmpData['unidad'] = $dataXml->xpath('//ns:unidad');
            $_tmpData['nombre'] = $dataXml->xpath('//ns:nombre');
            $_tmpData['direccion'] = $dataXml->xpath('//ns:direccion');
            $_tmpData['localidad'] = $dataXml->xpath('//ns:descLocalidad');
            $_tmpData['cp'] = $dataXml->xpath('//ns:cp');
            $_tmpData['telefono'] = $dataXml->xpath('//ns:telefono');
            $_tmpData['horariolv'] = $dataXml->xpath('//ns:horarioLV');
            $_tmpData['horarios'] = $dataXml->xpath('//ns:horarioS');
            $_tmpData['horariof'] = $dataXml->xpath('//ns:horarioF');
            $_tmpData['coorx'] = $dataXml->xpath('//ns:coorXWGS84');
            $_tmpData['coory'] = $dataXml->xpath('//ns:coorYWGS84');
            
            for ($indice = 0; $indice< count($_tmpData['unidad']); $indice++)
            {
                foreach($_tmpData as $_data => $_value)
                {
                    $_oficinas[$indice][$_data] = (string) $_value[$indice];
                }
            }
            
            if (empty($_oficinas)) return false;
            else return $_oficinas;
        }

        return false;
    }
    
    
    /**
     *  devuelve los datos de la oficina cuando se pide por codigo de oficina interno
     */
    public function dataOficinas($codOficina, $codPostal)
    {

        $_oficinas = $this->pedirOficinas($codPostal);

        if (!$_oficinas)
        {
            return false;
        } else {
            
            foreach ($_oficinas as $oficina)
            {
                if ($oficina['unidad'] == $codOficina)
                {
                    return $oficina; 
                }
            }
            
            return $codOficina;
                            
        }
        
    }
    
}