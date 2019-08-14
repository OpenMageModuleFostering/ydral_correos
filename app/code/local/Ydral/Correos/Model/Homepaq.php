<?php

/** 
 *
 */
class Ydral_Correos_Model_Homepaq extends Mage_Core_Model_Abstract
{
    
    private $_tokenTransaction;
    private $_gatewayHomepaq;
    
    
    
    /**
     *   prepara el selector de oficinas en el checkout
     *
     *  @param int $codpostal
     *  @return array|false
     */
    public function getHtmlPuntos ($homepaqUser, $storeId)
    {
        return $this->pedirPuntos($homepaqUser, $storeId);
    }
    
    
    /**
     *  url peticion
     */
    protected function getGatewayHomepaq()
    {
        if (!isset($this->_gatewayHomepaq) || empty($this->_gatewayHomepaq))
        {
            $this->_gatewayHomepaq = Mage::helper('correos')->getValueConfig('gateway_homepaq', 'general');
        }
        return $this->_gatewayHomepaq;
    }
    
    
    
    /**
     *   llama a correos para comprobar las oficinas relativas al codigo postal
     */
    protected function pedirPuntos ($homepaqUser, $storeId)
    {
        
        if (!Mage::helper('correos')->getValueConfig('active', 'general', $storeId)) return false;
        
        
        /**
         *  cadena de peticion
         */
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cor="http://www.correos.es/paq/correospaq"><soapenv:Header/><soapenv:Body><cor:getFavorites><user>' . Mage::helper('core')->htmlEscape(trim($homepaqUser)) . '</user><ip>' . Mage::helper('core/http')->getRemoteAddr()  . '</ip></cor:getFavorites></soapenv:Body></soapenv:Envelope>';
        $urlPeticion = $this->getGatewayHomepaq();
        $datosAcceso = Mage::helper('correos')->getValueConfig('usercorreos', 'general', $storeId).":".Mage::helper('correos')->getValueConfig('pwdcorreos', 'general', $storeId);
        

        /**
         *  peticion
         */ 
        Mage::helper('correos')->_logger('Peticion correospaq: ' . $xml);
        $xmlResponse = Mage::getModel('correos/curl')->callCurl($datosAcceso, $urlPeticion, $xml);        
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
            $dataXml->registerXPathNamespace('n2', 'http://jaxws.ws.paq.correos.es/');
            
            if ($dataXml->xpath('//errorCode')) 
            {
                $errorCode = $dataXml->xpath('//errorCode');
                $description = $dataXml->xpath('//description');
                
                $_puntos['errorCode'] = (string)$errorCode[0];
                $_puntos['description'] = (string)$description[0];
                
                return $_puntos;
            }
            
            $resultados = 0;
            
            
            $dataItems = $dataXml->xpath('//return/citypaqs/citypaq');
            $indice = 0;
            foreach($dataItems as $item)
            {
                
                $_tmpData = array();
                $_tmpData['address'] = $item->xpath('//return/citypaqs/citypaq/address');
                $_tmpData['admissionType'] = $item->xpath('//return/citypaqs/citypaq/admissionType');
                $_tmpData['alias'] = $item->xpath('//return/citypaqs/citypaq/alias');
                $_tmpData['city'] = $item->xpath('//return/citypaqs/citypaq/city');
                $_tmpData['code'] = $item->xpath('//return/citypaqs/citypaq/code');
                $_tmpData['defaultCitypaq'] = $item->xpath('//return/citypaqs/citypaq/defaultCitypaq');
                $_tmpData['latitude_wgs84'] = $item->xpath('//return/citypaqs/citypaq/latitude_wgs84');
                $_tmpData['longitude_wgs84'] = $item->xpath('//return/citypaqs/citypaq/longitude_wgs84');
                $_tmpData['postalCode'] = $item->xpath('//return/citypaqs/citypaq/postalCode');
                $_tmpData['state'] = $item->xpath('//return/citypaqs/citypaq/state');
                $_tmpData['schedule'] = $item->xpath('//return/citypaqs/citypaq/schedule');
                $_tmpData['streetType'] = '';
                $_tmpData['number'] = '';
                
                foreach($_tmpData as $_data => $_value)
                {
                    $_puntos[$resultados][$_data] = (isset($_value[$indice])?(string) str_replace('"', '', $_value[$indice]):'');
                }
                $indice++;
                $resultados++;
                
            }
            
            $dataItems = $dataXml->xpath('//return/homepaqs/homepaq');
            $indice = 0;
            foreach($dataItems as $item)
            {
                
                $_tmpData = array();
                $_tmpData['address'] = $item->xpath('//return/homepaqs/homepaq/address');
                $_tmpData['alias'] = $item->xpath('//return/homepaqs/homepaq/alias');
                $_tmpData['city'] = $item->xpath('//return/homepaqs/homepaq/city');
                $_tmpData['code'] = $item->xpath('//return/homepaqs/homepaq/homepaqCode');
                $_tmpData['defaultHomepaq'] = $item->xpath('//return/homepaqs/homepaq/defaultHomepaq');
                $_tmpData['number'] = $item->xpath('//return/homepaqs/homepaq/number');
                $_tmpData['streetType'] = $item->xpath('//return/homepaqs/homepaq/streetType');
                $_tmpData['postalCode'] = $item->xpath('//return/homepaqs/homepaq/postalCode');
                $_tmpData['state'] = $item->xpath('//return/homepaqs/homepaq/state');
                
                foreach($_tmpData as $_data => $_value)
                {
                    $_puntos[$resultados][$_data] = (isset($_value[$indice])?(string) str_replace('"', '', $_value[$indice]):'');
                }
                $indice++;
                $resultados++;
                
            }
            
            
            $tokens = $dataXml->xpath('//return/token');
            $this->_tokenTransaction = (string) $tokens[0];


            if (empty($_puntos)) return false;
            else return $_puntos;

        
        }

        return false;
        
    }
    
    public function getToken()
    {
        return $this->_tokenTransaction;
    }
    
    /**
     *  devuelve los datos del punto del campo guardado
     */
    public function dataPunto($order_info)
    {
        $_datos = explode('|', $order_info);
        if (is_array($_datos))
        {
            $_dataPunto['address'] = $_datos[0];
            $_dataPunto['cp'] = $_datos[1];
            $_dataPunto['city'] = $_datos[2];
            $_dataPunto['region'] = $_datos[3];
            $_dataPunto['code'] = $_datos[4];
            
            return $_dataPunto;
            
        } else {
            return false;
        }
    }
        
    /**
     *
     */
    public function getStatesWithCitypaq($storeId)
    {
        
        /**
         *  cadena de peticion
         */
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cor="http://www.correos.es/paq/correospaq"><soapenv:Body><cor:getStatesWithCitypaq/></soapenv:Body></soapenv:Envelope>';
        $urlPeticion = $this->getGatewayHomepaq();
        $datosAcceso = Mage::helper('correos')->getValueConfig('usercorreos', 'general', $storeId).":".Mage::helper('correos')->getValueConfig('pwdcorreos', 'general', $storeId);
        

        /**
         *  peticion
         */ 
        Mage::helper('correos')->_logger('Peticion correospaq: ' . $xml);
        $xmlResponse = Mage::getModel('correos/curl')->callCurl($datosAcceso, $urlPeticion, $xml);        
        
        $_info = array();
        if ( (strlen(trim($xmlResponse)) > 0) && Mage::helper('correos')->is_valid_xml($xmlResponse) )
        {
            $dataXml = simplexml_load_string($xmlResponse, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");            
            $dataXml->registerXPathNamespace('n2', 'http://jaxws.ws.paq.correos.es/');

            $dataItems = $dataXml->xpath('//return/states/state');
            $indice = 0;
            foreach($dataItems as $item)
            {
                
                $_tmpData = array();
                $_tmpData['code'] = $item->xpath('//return/states/state/code');
                $_tmpData['name'] = $item->xpath('//return/states/state/name');
                
                foreach($_tmpData as $_data => $_value)
                {
                    $_info[$indice][$_data] = (string) $_value[$indice];
                }
                $indice++;
                
            }
            
            if (empty($_info)) return false;
            else return $_info;
        
        }
        
    }
    
    
    /**
     *
     */
    public function getCitypaqs($type, $code, $storeId)
    {
        
        if (!is_numeric($code)) return false;
        
        /**
         *  cadena de peticion
         */
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cor="http://www.correos.es/paq/correospaq"><soapenv:Header/><soapenv:Body><cor:getCitypaqs><'.$type.'>'.$code.'</'.$type.'></cor:getCitypaqs></soapenv:Body></soapenv:Envelope>';
        $urlPeticion = $this->getGatewayHomepaq();
        $datosAcceso = Mage::helper('correos')->getValueConfig('usercorreos', 'general', $storeId).":".Mage::helper('correos')->getValueConfig('pwdcorreos', 'general', $storeId);
        

        /**
         *  peticion
         */ 
        Mage::helper('correos')->_logger('Peticion correospaq: ' . $xml);
        $xmlResponse = Mage::getModel('correos/curl')->callCurl($datosAcceso, $urlPeticion, $xml);  
        
        $_info = array();
        if ( (strlen(trim($xmlResponse)) > 0) && Mage::helper('correos')->is_valid_xml($xmlResponse) )
        {
            $dataXml = simplexml_load_string($xmlResponse, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");            
            $dataXml->registerXPathNamespace('n2', 'http://jaxws.ws.paq.correos.es/');

            $dataItems = $dataXml->xpath('//return/citypaq');
            
            $indice = 0;
            foreach($dataItems as $item)
            {
                
                $_tmpData = array();
                $_tmpData['alias'] = $item->xpath('//return/citypaq/alias');
                $_tmpData['postalCode'] = $item->xpath('//return/citypaq/postalCode');
                $_tmpData['admissionType'] = $item->xpath('//return/citypaq/admissionType');
                $_tmpData['code'] = $item->xpath('//return/citypaq/code');
                $_tmpData['address'] = $item->xpath('//return/citypaq/address');
                $_tmpData['schedule'] = $item->xpath('//return/citypaq/schedule');
                $_tmpData['latitude_wgs84'] = $item->xpath('//return/citypaq/latitude_wgs84');
                $_tmpData['city'] = $item->xpath('//return/citypaq/city');
                $_tmpData['longitude_wgs84'] = $item->xpath('//return/citypaq/longitude_wgs84');
                $_tmpData['number'] = $item->xpath('//return/citypaq/number');
                $_tmpData['state'] = $item->xpath('//return/citypaq/state');
                $_tmpData['streetType'] = $item->xpath('//return/citypaq/streetType');
                
                foreach($_tmpData as $_data => $_value)
                {
                    $_info[$indice][$_data] = (string) $_value[$indice];
                }
                $indice++;
                
            }
            
            if (empty($_info)) return false;
            else return $_info;
        
        }
        
    }
    
    
    /**
     *
     */
    public function getUrl($type, $user, $favorite = '', $storeId)
    {
        
        /**
         *  cadena de peticion
         */
        if (!empty($favorite))
        {
            $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cor="http://www.correos.es/paq/correospaq"><soapenv:Body><cor:getUrl><user>'.$user.'</user><operationType>'.$type.'</operationType><urlCallBack>'.Mage::getUrl('correos/citypaq/finalfavorite').'</urlCallBack><favorite>'.$favorite.'</favorite></cor:getUrl><integrationMode>I</integrationMode></soapenv:Body></soapenv:Envelope>';
        } else {
            $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cor="http://www.correos.es/paq/correospaq"><soapenv:Body><cor:getUrl><user>'.$user.'</user><operationType>'.$type.'</operationType></cor:getUrl></soapenv:Body></soapenv:Envelope>';
        }
        $urlPeticion = $this->getGatewayHomepaq();
        $datosAcceso = Mage::helper('correos')->getValueConfig('usercorreos', 'general', $storeId).":".Mage::helper('correos')->getValueConfig('pwdcorreos', 'general', $storeId);
        

        /**
         *  peticion
         */ 
        Mage::helper('correos')->_logger('Peticion correospaq: ' . $xml);
        $xmlResponse = Mage::getModel('correos/curl')->callCurl($datosAcceso, $urlPeticion, $xml);  
        
        
        $_info = array();
        if ( (strlen(trim($xmlResponse)) > 0) && Mage::helper('correos')->is_valid_xml($xmlResponse) )
        {
            $dataXml = simplexml_load_string($xmlResponse, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");            
            $dataXml->registerXPathNamespace('n2', 'http://jaxws.ws.paq.correos.es/');

            $_url = $dataXml->xpath('//return/url');
            $url['url'] = (string) $_url[0];
            
            return $url;
        
        }
        
    }
    
}