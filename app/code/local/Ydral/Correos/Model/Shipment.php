<?php

/**
 *  Crea el preregistro del paquete en Correos
 */
class Ydral_Correos_Model_Shipment extends Mage_Sales_Model_Order_Shipment
{
    
    protected $shippingMethod = '';
    protected $kahalaCountries = array ('AU', 'CN', 'KR', 'US', 'GG', 'HK', 'JP', 'JE', 'IM', 'GB',);
    protected $eupeanCountries = array ('AT', 'BE', 'BG', 'CH', 'CY', 'CZ', 'DE', 'DK', 'EE', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IS', 'IT', 'LI', 'LT', 'LU', 'LV', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK', 'TR', 'UK',);
    
    protected $_medidaPeso;
    protected $_medidaLargo;
    protected $_medidaAlto;
    protected $_medidaAncho;
    protected $_goodsDua;
    protected $_todoRiesgo;
    protected $_volumetrico;
    protected $_observaciones;
    
    protected $_storeId;
    
    
    /**
     *
     */
    protected function getStoreId()
    {
        if (!isset($this->_storeId)) 
        {
            $_order = $this->getOrder();
            $this->_storeId = $_order->getStoreId();
        }
        return $this->_storeId;
    }
    
    
    /**
     *   solicitud de peticion de datos previa a la generacion de envio
     */
    protected function _beforeSave()
    {
        $envio  = array();
        $_order = $this->getOrder();
        
        /**
         *  Correos Check
         */
        $this->shippingMethod = $_order->getShippingMethod();
        if (!in_array($this->shippingMethod, Mage::helper('correos')->getAllowedMethods()))
        {
            return parent::_beforeSave();
        }
        
        /**
         *   solo para correos, otros metodos siguen igual
         */
        $_params    = Mage::getSingleton('core/app')->getRequest()->getPost();
        $_shipment  = $_params['shipment'];
        //$number     = Mage::getSingleton('core/app')->getRequest()->getPost('number');
        //$carrier    = Mage::getSingleton('core/app')->getRequest()->getPost('carrier');
        //$shipment   = str_replace(',', '.', Mage::getSingleton('core/app')->getRequest()->getPost('shipment'));

        if (isset($_shipment['remitente_select']))    { $remitente  = (int)$_shipment['remitente_select']; } else { $remitente = ''; }
        if (isset($_shipment['correos_peso']))        { $this->_medidaPeso      = $_shipment['correos_peso']; } else { $this->_medidaPeso = ''; }
        if (isset($_shipment['correos_largo']))       { $this->_medidaLargo     = (int)$_shipment['correos_largo']; } else { $this->_medidaLargo = ''; }
        if (isset($_shipment['correos_ancho']))       { $this->_medidaAncho     = (int)$_shipment['correos_ancho']; } else { $this->_medidaAncho = ''; }
        if (isset($_shipment['correos_alto']))        { $this->_medidaAlto      = (int)$_shipment['correos_alto']; } else { $this->_medidaAlto = ''; }
        if (isset($_shipment['correos_goods']))       { $this->_goodsDua        = (int)$_shipment['correos_goods']; } else { $this->_goodsDua = ''; }
        if (isset($_shipment['correos_todoriesgo']))  { $this->_todoRiesgo      = $_shipment['correos_todoriesgo']; } else { $this->_todoRiesgo = ''; }
        if (isset($_shipment['correos_volumetrico'])) { $this->_volumetrico     = $_shipment['correos_volumetrico']; } else { $this->_volumetrico = ''; }
        if (isset($_shipment['correos_observacion'])) { $this->_observaciones   = $_shipment['correos_observacion']; } else { $this->_observaciones = ''; }
        
        /**
         *  validacion incompatibilidad homepaq y contrareembolso
         */
        if (($this->shippingMethod == 'homepaq48_homepaq48' || $this->shippingMethod == 'homepaq72_homepaq72') && Mage::helper('correos')->checkCashondelivery($_order->getPayment()->getMethodInstance()->getCode()))
        {
            Mage::throwException(
                    Mage::helper('correos')->__('CorreosPaq no es compatible con contrareembolsos.')
                );
            return false;
        }
        
        
        /**
         *  Proceso PRE-REGISTRO Correos
         */
        try
        {
        
            /**
            *   informacion de recogida
            *   TO-DO: cuando no haya datos permitir finalizar el registro de forma normal aunque sea de Correos.
            */        
            $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $_order->getRealOrderId())->getFirstItem();
            if ($_dataRecogida->getId() == '') 
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No hay datos asociados a este pedido de Correos.')
                );
                return false;
            }

        
            /**
             *
             */
            $_helper = Mage::helper('correos/preregistro');
            
         
            /**
             *  datos
             */
            $_data['fechaOperacion'] = date('d-m-Y H:i:s', strtotime($_order->getCreatedAt()));
            $_data['dataAuth'] = $this->getAuthXml();
            if (Mage::helper('correos')->getValueConfig('multisender', 'remitente', $this->getStoreId()))
            {
                $_data['datosRemitente'] = $this->getDataMultiRemitente($remitente);
            } else {
                $_data['datosRemitente'] = $this->getDataRemitente();
            }
            if ($_order->getShippingMethod() == 'homepaq48_homepaq48' || $_order->getShippingMethod() == 'homepaq72_homepaq72')
            {
                $_data['DatosDestinatario'] = $this->getDataDestinatario($_order, $_dataRecogida->getMovilAsociado(), $_dataRecogida->getLanguage());
                $_data['DatosDestinatario'] = $this->getDataDestinatarioHomepaq($_order, $_data['DatosDestinatario'], $_dataRecogida);
            } else {
                $_data['DatosDestinatario'] = $this->getDataDestinatario($_order, $_dataRecogida->getMovilAsociado(), $_dataRecogida->getLanguage());
            }
            $_data['DatosEnvio'] = $this->getDataEnvio($_order, $_dataRecogida);
            $_data['observaciones'] = $this->_observaciones;
            
            
            
            /**
             *  verificaciones de pais
             */
            if (($_order->getShippingAddress()->getCountryId() != 'ES') && ($this->shippingMethod != 'correosinter_correosinter'))
            {
                if ($_order->getShippingAddress()->getCountryId() == 'PT' && ($this->shippingMethod != 'envio48_envio48' && $this->shippingMethod != 'envio72_envio72'))
                {
                    Mage::throwException(
                        Mage::helper('correos')->__('Error en el envío por país/método de envío no permitido [%s].', $_order->getShippingAddress()->getCountryId())
                    );
                    return false;
                }
            }


            /**
             *  xml
             */
            $xmlSend = $_helper->getXmlPreregistro($_data);
            
           
            $peticionCorreos = $this->preregistroCorreos($xmlSend, 'Preregistro');
            if ($peticionCorreos == '')
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No se ha podido contactar con el servidor de Correos. Respuesta vac&iacute;a.')
                );
                return false;
            }
            
            
            /**
             *
             */
            if (!Mage::helper('correos')->checkAuthLoginResponse($peticionCorreos))
            {
                Mage::throwException(
                    Mage::helper('correos')->__('La pasarela de Correos ha devuelto un error de acceso. Compruebe sus credenciales.')
                );
                return false;
            }
        

            /**
             *
             */
            $dataXml = simplexml_load_string($peticionCorreos);
            if (!$dataXml->registerXPathNamespace('respuesta', 'http://www.correos.es/iris6/services/preregistroetiquetas'))
            {
                //  ha devuelto un error;
                Mage::throwException(
                    Mage::helper('correos')->__('Error en los datos devueltos por Correos.')
                );
                return false;
            }

            $_codExpedicion = $dataXml->xpath('//respuesta:CodExpedicion');
            $_codEnvio = $dataXml->xpath('//respuesta:Bulto');
            $_codError = $dataXml->xpath('//respuesta:Resultado');
            
            
            if (isset($_codError) && $_codError[0] == '0')
            {
                // correcto
                $_datosBulto = $dataXml->xpath('//respuesta:Bulto');
                Mage::getSingleton('adminhtml/session')->addNotice('Se ha creado la etiqueta de env&iacute;o para Correos.');
                if (isset($_codExpedicion[0]))
                {
                    Mage::getSingleton('adminhtml/session')->addNotice('C&oacute;digo de expedici&oacute;n: ' . $_codExpedicion[0]);
                }
                Mage::getSingleton('adminhtml/session')->addNotice('C&oacute;digo de env&iacute;o: ' . $_codEnvio[0]->CodEnvio);
                
                $track = Mage::getModel('sales/order_shipment_track')
                            ->setNumber($_codEnvio[0]->CodEnvio)
                            ->setCarrierCode($_order->getShippingCarrier()->getCarrierCode())
                            ->setTitle('Seguimiento Correos');
        
                $this->addTrack($track);
                
                
                /**
                 *  Status
                 */
                $_order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
                $_order->setData('status', 'processing_correos');
                
                
                /**
                 *  Registro de informacion
                 */
                $_dataRegistro = array();
                $_dataRegistro['order_id'] = $_order->getId();
                $_dataRegistro['real_order_id'] = $_data['DatosEnvio']['ReferenciaCliente'];
                $_dataRegistro['num_registro'] = (string)$_codEnvio[0]->CodEnvio;
                $_dataRegistro['peso'] = $_data['DatosEnvio']['Peso_Valor'];
                $_dataRegistro['medida_ancho'] = strip_tags($_data['DatosEnvio']['Ancho']);
                $_dataRegistro['medida_alto'] = strip_tags($_data['DatosEnvio']['Alto']);
                $_dataRegistro['medida_fondo'] = strip_tags($_data['DatosEnvio']['Largo']);

                $preRegTransaction = Mage::getModel('correos/registro')->setData($_dataRegistro);
                $preRegTransaction->save();
                 
                                 
            } else {
                
                $_msjError = $dataXml->xpath('//respuesta:DescError');
                
                // error
                Mage::throwException(
                    Mage::helper('sales')->__('Error en la creaci&oacute;n de la operaci&oacute;n de preregistro. ' . $_msjError[0])
                );
                return false;
                
            }
            

            $canSave = parent::_beforeSave();
            return $canSave;
            
            
        
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
        
    }
    
    
    
    
    /**
     *  XML Auth Correos
     */
    protected function getAuthXml($prefix = '')
    {
        if (Mage::helper('correos')->getValueConfig('etiquetador', 'general', $this->getStoreId()) != '')
        {
            $_dataAuth = '<'.$prefix.'NumContrato></'.$prefix.'NumContrato><'.$prefix.'NumCliente></'.$prefix.'NumCliente><'.$prefix.'CodEtiquetador>'.trim(Mage::helper('correos')->getValueConfig('etiquetador', 'general', $this->getStoreId())).'</'.$prefix.'CodEtiquetador>';
        } elseif ( (Mage::helper('correos')->getValueConfig('contrato', 'general', $this->getStoreId()) != '') && (Mage::helper('correos')->getValueConfig('numcliente', 'general', $this->getStoreId()) != '') ) {
            $_dataAuth = '<'.$prefix.'NumContrato>'.trim(Mage::helper('correos')->getValueConfig('contrato', 'general', $this->getStoreId())).'</'.$prefix.'NumContrato><'.$prefix.'NumCliente>'.trim(Mage::helper('correos')->getValueConfig('numcliente', 'general', $this->getStoreId())).'</'.$prefix.'NumCliente><'.$prefix.'CodEtiquetador></'.$prefix.'CodEtiquetador>';
        } else {
            Mage::throwException(
                Mage::helper('correos')->__('No se han definido los datos de etiquetador o usuario/password del servicio. Error.')
            );
            return false;
        }
        
        return $_dataAuth;
    }
    
    
    /**
     *
     */
    protected function getDataRemitente()
    {
        
        $_camposObligatorios = array (
                                'Direccion',
                                'Localidad',
                                'CP',
                                );
        
        //  tipoIdentificacion
        $data['Nombre']            = substr(Mage::helper('correos')->getValueConfig('nombre', 'remitente', $this->getStoreId()), 0, 300);
        $data['Apellidos']         = substr(Mage::helper('correos')->getValueConfig('apellidos', 'remitente', $this->getStoreId()), 0, 50);
        $data['Nif']               = substr(Mage::helper('correos')->getValueConfig('nif', 'remitente', $this->getStoreId()), 0, 15);
        $data['Empresa']           = substr(Mage::helper('correos')->getValueConfig('empresa', 'remitente', $this->getStoreId()), 0, 150);
        $data['Personacontacto']   = substr(Mage::helper('correos')->getValueConfig('personacontacto', 'remitente', $this->getStoreId()), 0, 150);
        
        //  tipoDireccion
        $data['Direccion']         = substr(Mage::helper('correos')->getValueConfig('direccion', 'remitente', $this->getStoreId()), 0, 100);
        $data['Localidad']         = substr(Mage::helper('correos')->getValueConfig('localidad', 'remitente', $this->getStoreId()), 0, 100);
        $provincia = Mage::getModel('directory/region')->load(Mage::helper('correos')->getValueConfig('region_id', 'remitente', $this->getStoreId()))->getName();
        $data['Provincia']         = substr($provincia, 0, 40);
        
        //
        $data['CP']                = '<CP><![CDATA[' . substr(Mage::helper('correos')->getValueConfig('codpostal', 'remitente', $this->getStoreId()), 0, 5) . ']]></CP>';
        $data['Telefono']          = substr(Mage::helper('correos')->getValueConfig('telefono', 'remitente', $this->getStoreId()), 0, 15);
        $data['Email']             = substr(Mage::helper('correos')->getValueConfig('email', 'remitente', $this->getStoreId()), 0, 50);
        
        //  tipoSMS
        $data['Sms']               = substr(Mage::helper('correos')->getValueConfig('telefonosms', 'remitente', $this->getStoreId()), 0, 12);
        if (!Mage::helper('correos')->validarMovil($data['Sms']))
        {
            $data['Sms'] = '';
        }
        
        //  checks
        if (empty($data['Nombre']) && empty($data['Empresa']))
        {
            Mage::throwException(
                Mage::helper('correos')->__('Uno de los campos de remitente Nombre o Empresa es obligatorio.')
            );
            return false;
        }
        if (!$this->checkMandatoryFields($data, $_camposObligatorios, 'remitente'))
        {
            return false;
        }
        
        return $data;
        
    }
    
    
    /**
     *
     */
    protected function getDataMultiRemitente($remitente)
    {

        $remitenteData = Mage::getModel('correos/remitente')->load($remitente);
        if (($remitenteData->getNombre() == '') && ($remitenteData->getEmpresa() == ''))
        {
            Mage::throwException(
                Mage::helper('correos')->__('Uno de los campos de remitente Nombre o Empresa es obligatorio.')
            );
            return false;
        }
        
        $_camposObligatorios = array (
                                'Direccion',
                                'Localidad',
                                'CP',
                                );
        
        //  tipoIdentificacion
        $data['Nombre']            = substr($remitenteData->getNombre(), 0, 300);
        $data['Apellidos']         = substr($remitenteData->getApellidos(), 0, 50);
        $data['Nif']               = substr($remitenteData->getDni(), 0, 15);
        $data['Empresa']           = substr($remitenteData->getEmpresa(), 0, 150);
        $data['Personacontacto']   = substr($remitenteData->getPersonaContacto(), 0, 150);
        
        //  tipoDireccion
        $data['Direccion']         = substr($remitenteData->getDireccion(), 0, 100);
        $data['Localidad']         = substr($remitenteData->getLocalidad(), 0, 100);
        $provincia = Mage::getModel('directory/region')->load($remitenteData->getProvincia())->getName();
        $data['Provincia']         = substr($provincia, 0, 40);
        
        //
        $data['CP']                = '<CP><![CDATA[' . substr($remitenteData->getCp(), 0, 5) . ']]></CP>';
        $data['Telefono']          = substr($remitenteData->getTelefono(), 0, 15);
        $data['Email']             = substr($remitenteData->getEmail(), 0, 50);
        
        //  tipoSMS
        $data['Sms']               = substr($remitenteData->getTelefonoMovil(), 0, 12);
        if (!Mage::helper('correos')->validarMovil($data['Sms']))
        {
            $data['Sms'] = '';
        }
        
        //  checks
        if (!$this->checkMandatoryFields($data, $_camposObligatorios, 'remitente'))
        {
            return false;
        }
        
        return $data;
        
    }
    
    
    /**
     *
     */
    public function getDataDestinatario($order, $movil, $language = '1')
    {

        $_camposObligatorios = array (
                                //'Nombre',
                                'Direccion',
                                'Localidad',
                                );

        //  tipoIdentificacion
        if ($order->getShippingAddress()->getCompany() && ($order->getShippingAddress()->getCompany() != ''))
        {
            $data['Nombre']             = '';
            $data['Apellidos']          = '';
            $data['Empresa']            = '<Empresa><![CDATA[' . substr($order->getShippingAddress()->getCompany(), 0, 150) . ']]></Empresa>';
            $data['PersonaContacto']    = '<PersonaContacto><![CDATA[' . substr($order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname(), 0, 150) . ']]></PersonaContacto>';
        } else {
            $data['Nombre']             = substr($order->getShippingAddress()->getFirstname(), 0, 300);
            $data['Apellidos']          = substr($order->getShippingAddress()->getLastname(), 0, 50);
            $data['Empresa']            = '';
            $data['PersonaContacto']    = '';
        }
        
        //  tipoDireccion
        $data['Direccion']         = substr($order->getShippingAddress()->getStreetFull(), 0, 100);
        if ($this->shippingMethod != 'envio48_envio48' && $this->shippingMethod != 'envio72_envio72')
        {
            $data['DireccionAlt']      = substr($order->getShippingAddress()->getStreet(2), 0, 45);
        } else {
            $data['DireccionAlt']      = '';
        }
        $data['Localidad']         = substr($order->getShippingAddress()->getCity(), 0, 100);
        $data['Provincia']         = substr($order->getShippingAddress()->getRegion(), 0, 40);
        
        // 
        if (!$this->isInternationalOrder($order))
        {
            $data['CP']            = '<CP><![CDATA[' . Mage::helper('correos')->getCodigoPostal($order) . ']]></CP>';
        } else {
            $data['CP']            = '';
        }
        $data['ZIP']               = substr($order->getShippingAddress()->getPostcode(), 0, 10); // Mage::helper('correos')->getCodigoPostal($order, 10);
        $data['Pais']              = substr($order->getShippingAddress()->getCountryId(), 0, 2);
        if ($this->shippingMethod == 'homepaq48_homepaq48' || $this->shippingMethod == 'homepaq72_homepaq72')
        {
            $data['Telefono']          = substr($movil, 0, 15);
        } else {
            $data['Telefono']          = substr($order->getShippingAddress()->getTelephone(), 0, 15);
        }
        $data['Email']             = substr($order->getShippingAddress()->getEmail(), 0, 50);
        
        //  tipoSMS
        if (empty($movil))
        {
            //$_destinatarioTelefono = substr($_order->getShippingAddress()->getTelephone(), 0, 12);
            $data['Sms'] = substr($order->getShippingAddress()->getTelephone(), 0, 12);      // las ultimas 9 por si pone +,0...
        } else {
            //$_destinatarioTelefono = substr($_dataRecogida['movil_asociado'], 0, 9);
            $data['Sms'] = substr($movil, 0, 12);      // las ultimas 9 por si pone +,0...
        }
        /*
        if ($this->shippingMethod == 'recogeroficina48_recogeroficina48' || $this->shippingMethod == 'recogeroficina72_recogeroficina72')
        {
            if (!Mage::helper('correos')->validarMovil($data['Sms']))
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No se puede registrar un pedido con el n&uacute;mero de tel&eacute;fono aportado (%s), porque no se podr&aacute; notificar a este n&uacute;mero.', $data['Sms'])
                );
                return false;
            }
        }
        */
        if (!Mage::helper('correos')->validarMovil($data['Sms']))
        {
            $data['Sms'] = '';
        }
        if (empty($language)) $data['Idioma'] = '1';
        else $data['Idioma'] = $language;
        
        //  checks
        if (!$this->isInternationalOrder($order))
        {
            $_camposObligatorios[] = 'CP';
        } else {
            if (in_array($order->getShippingAddress()->getCountryId(), $this->kahalaCountries)) $_camposObligatorios[] = 'ZIP';
            elseif (in_array($order->getShippingAddress()->getCountryId(), $this->eupeanCountries)) $_camposObligatorios[] = 'ZIP';
            else $_camposObligatorios[] = 'Pais';
        }
        if (!$this->checkMandatoryFields($data, $_camposObligatorios, 'destinatario'))
        {
            return false;
        }
        if ($this->shippingMethod == 'recogeroficina48_recogeroficina48' || $this->shippingMethod == 'recogeroficina72_recogeroficina72')
        {
            $_camposObligatorios[] = 'Email';
            $_camposObligatorios[] = 'Sms';
        }
        
        return $data;
        
    }
    
    
    /**
     *
     */
    public function getDataDestinatarioHomepaq($order, $dataDestinatario, $dataRecogida)
    {
        
        //  Punto HomePaq
        $_puntoHomePaq = $dataRecogida->getInfoPunto();
        if (!$_puntoHomePaq) return $dataDestinatario;
        $_puntoHomePaq = explode('|', $_puntoHomePaq);
        
        //  tipoDireccion
        $dataDestinatario['Direccion']         = substr($_puntoHomePaq[0], 0, 100);
        $dataDestinatario['DireccionAlt']      = '';
        $dataDestinatario['Localidad']         = substr($_puntoHomePaq[2], 0, 100);
        $dataDestinatario['Provincia']         = substr($_puntoHomePaq[3], 0, 40);
        $dataDestinatario['CP']                = '<CP><![CDATA[' . substr($_puntoHomePaq[1], 0, 5) . ']]></CP>';
        $dataDestinatario['ZIP']               = substr($_puntoHomePaq[1], 0, 10);
        
        return $dataDestinatario;
    }
     

    /**
     *
     */
    public function getDataEnvio($order, $_dataBd)
    {        
        
        //  opciones segun envio
        if ($this->shippingMethod == 'recogeroficina48_recogeroficina48') {
            $data['CodProducto']        = 'S0236';
            $data['ModalidadEntrega']   = 'LS';
            $data['OficinaElegida']     = "<OficinaElegida>{$_dataBd->getCorreosRecogida()}</OficinaElegida>";
        } elseif ($this->shippingMethod == 'recogeroficina72_recogeroficina72') {
            $data['CodProducto']        = 'S0133';
            $data['ModalidadEntrega']   = 'LS';
            $data['OficinaElegida']     = "<OficinaElegida>{$_dataBd->getCorreosRecogida()}</OficinaElegida>";
        } elseif ($this->shippingMethod == 'envio48_envio48') {
            $data['CodProducto']        = 'S0235';
            $data['ModalidadEntrega']   = 'ST';
            $data['OficinaElegida']     = '';
        } elseif ($this->shippingMethod == 'envio72_envio72') {
            $data['CodProducto']        = 'S0132';
            $data['ModalidadEntrega']   = 'ST';
            $data['OficinaElegida']     = '';
        } elseif ($this->shippingMethod == 'correosinter_correosinter') {
            $data['CodProducto']        = 'S0030';
            $data['ModalidadEntrega']   = 'ST';
            $data['OficinaElegida']     = '';
        } elseif ($this->shippingMethod == 'homepaq48_homepaq48') {
            if (strtolower(substr(trim($_dataBd->getHomepaqId()), -1)) == 'd') { 
                $data['CodProducto']        = 'S0175';
            } else {    //  p        
                $data['CodProducto']        = 'S0176';
            }
            $data['ModalidadEntrega']   = 'ST';
            $data['OficinaElegida']     = '';
        } elseif ($this->shippingMethod == 'homepaq72_homepaq72') {
            if (strtolower(substr(trim($_dataBd->getHomepaqId()), -1)) == 'd') { 
                $data['CodProducto']        = 'S0177';
            } else {    //  p
                $data['CodProducto']        = 'S0178';
            }
            $data['ModalidadEntrega']   = 'ST';
            $data['OficinaElegida']     = '';
        }
        $data['ReferenciaCliente']  = $order->getIncrementId();
        $data['TipoFranqueo']       = 'FP';
        
        
        /**
         *  PESO
         */
        //  ListaTipoPeso -> tipoPeso
        $peso = 0;
        if (isset($this->_medidaPeso) && !empty($this->_medidaPeso))
        {
            $peso = $this->_medidaPeso;
            $peso = intval($peso*1000);
            if ($peso == 0) { $peso = 1; }  // peso minimo
        } else {
            $peso = Mage::helper('correos')->getPesoPaquete($order);
        }
        
        $data['Peso_Tipo']  = 'R';
        $data['Peso_Valor'] = $peso;    
        unset($peso);
        
        
        
        /**
         *  MEDIDAS
         */
        $data['Largo'] = $data['Ancho'] = $data['Alto'] = '';
        if (isset($this->_medidaLargo) && !empty($this->_medidaLargo))
        {
            $data['Largo'] = '<Largo>' . intval($this->_medidaLargo) . '</Largo>';
        }
        if (isset($this->_medidaAncho) && !empty($this->_medidaAncho))
        {
            $data['Ancho'] = '<Ancho>' . intval($this->_medidaAncho) . '</Ancho>';
        }
        if (isset($this->_medidaAlto) && !empty($this->_medidaAlto))
        {
            $data['Alto'] = '<Alto>' . intval($this->_medidaAlto) . '</Alto>';
        }
        
        
        
        //  ValoresAnadidos
        $data['ValoresAnadidos'] = $this->getDataAnadidos($order);
        
        //
        if ($this->isInternationalOrder($order))
        {
            $data['InstruccionesDevolucion']    = '<InstruccionesDevolucion>D</InstruccionesDevolucion>';
        } else {
            $data['InstruccionesDevolucion']    = '';
        }
        
        //  TipoAduana
        $data['Aduana'] = $this->getDataAduana($order);
        
        
        //  HomePaq
        $data['Homepaq'] = $this->getDataHomepaq($_dataBd);

        
        
        return $data;
        
    }


    /**
     *
     */
    protected function getDataAnadidos($order)
    {
        
        $_dataCorreos = Mage::getModel('correos/recoger')->getCheckoutData('order', $order->getRealOrderId())->getFirstItem();
        
        
        //  Seguro
        if (isset($this->_todoRiesgo) && !empty($this->_todoRiesgo) && is_numeric($this->_todoRiesgo)) 
        {
            $data['ImporteSeguro']    = "<ImporteSeguro>".($this->_todoRiesgo*100)."</ImporteSeguro>";
        }
        elseif (Mage::helper('correos')->getValueConfig('seguro', 'paquete', $this->getStoreId()))
        {
            $data['ImporteSeguro']    = "<ImporteSeguro>".(Mage::helper('correos')->getValueConfig('importeseguro', 'paquete', $this->getStoreId())*100)."</ImporteSeguro>";
        } else {
            $data['ImporteSeguro']    = '';
        }

        //  tipoReembolso
        if (Mage::helper('correos')->checkCashondelivery($order->getPayment()->getMethodInstance()->getCode()))
        {
            $_total = $order->getBaseGrandTotal();
            $_total = number_format($_total, 2, '.', '');
            if (!$this->isInternationalOrder($order) && $_total > 1000)
            {
                Mage::throwException(
                    Mage::helper('correos')->__('El pedido a reembolsar es mayor de 1000€ y no puede enviarse bajo Correos.')
                );
                return false;
            } else {
                $_total = $_total * 100;
            }
            
            $data['Reembolso']  = '<Reembolso>';
            $data['Reembolso'] .= '<TipoReembolso>RC</TipoReembolso>';
            $data['Reembolso'] .= '<Importe>' . $_total . '</Importe>';
            $data['Reembolso'] .= '<NumeroCuenta>' . substr(Mage::helper('correos')->getValueConfig('cuentareembolso', 'paquete', $this->getStoreId()), 0, 20) . '</NumeroCuenta>';
            $data['Reembolso'] .= '<Transferagrupada>S</Transferagrupada>';
            $data['Reembolso'] .= '</Reembolso>';
        } else {
            $data['Reembolso'] = '';
        }
        
        
        //  Entrega
        if ($this->isInternationalOrder($order)){
            $data['EntregaExclusivaDestinatario']    = "";
        } elseif (Mage::helper('correos')->getValueConfig('entregaexclusiva', 'paquete', $this->getStoreId())) {
            $data['EntregaExclusivaDestinatario']    = "<EntregaExclusivaDestinatario>S</EntregaExclusivaDestinatario>";
            $data['EntregaExclusivaDestinatario']   .= "<PruebaEntrega><Formato>1</Formato></PruebaEntrega>";
        } else {
            $data['EntregaExclusivaDestinatario']    = "<EntregaExclusivaDestinatario>N</EntregaExclusivaDestinatario>";
        }
        
        if ($this->shippingMethod == 'envio48_envio48')
        {
            $data['FranjaHorariaConcertada'] = '<FranjaHorariaConcertada>' . $_dataCorreos['horario'] . '</FranjaHorariaConcertada>';
        } else {
            $data['FranjaHorariaConcertada'] = '';
        }
       
        return $data;
        
    }
    
    
    /**
     *
     */
    protected function getDataAduana($order)
    {
        
        $data['tipoAduana'] = '';
        
        //  tipoAduana
        $_origen    = Mage::getModel('directory/region')->load(Mage::helper('correos')->getValueConfig('region_id', 'remitente', $this->getStoreId()))->getName();
        $_destino   = $order->getShippingAddress()->getRegion();
        $storeId    = $order->getStoreId();
        
        
        if ($this->shippingMethod == 'homepaq48_homepaq48' || $this->shippingMethod == 'homepaq72_homepaq72')
        {
            $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $order->getRealOrderId())->getFirstItem();
            $_infoPunto = Mage::getModel('correos/homepaq')->dataPunto($_dataRecogida['info_punto']);
            if (is_array($_infoPunto)) { 
                $_destino = $_infoPunto['region'];
            }
        }
        

        $_obligatorio = false;
        $_dataAduana  = false;
        $provinciasDua = Mage::helper('correos/dua')->getProvinciasDua($storeId);
        if (in_array($_origen, $provinciasDua) && ($_origen != $_destino))
        {
            $_obligatorio = true;
            $_dataAduana = true;
        } elseif ($this->isInternationalOrder($order)) { 
            $_obligatorio = true;
            $_dataAduana = true;
        } elseif ( in_array($_destino, $provinciasDua) && ($_origen != $_destino) ) {
            $_obligatorio = true;
            $_dataAduana = true;
        }
        
        

        if ($_obligatorio)
        {
            $data['tipoAduana'] = '<Aduana>';
            $data['tipoAduana'].= '<TipoEnvio>2</TipoEnvio>';
            $data['tipoAduana'].= '<EnvioComercial>S</EnvioComercial>';
            if ($order->getBaseGrandTotal() >= 500)
            {
                $data['tipoAduana'].= '<FacturaSuperiora500>S</FacturaSuperiora500>';
                $data['tipoAduana'].= '<DUAConCorreos>N</DUAConCorreos>';
            } else {
                $data['tipoAduana'].= '<FacturaSuperiora500>N</FacturaSuperiora500>';
            }
            if ($_dataAduana)
            {
                //
                $data['tipoAduana'].= $this->getDataAduanaElements($order);
                $data['tipoAduana'].= '<Factura>' . (Mage::helper('correos')->getValueConfig('paquetefactura', 'paquete', $this->getStoreId())?'S':'N') . '</Factura>';
                $data['tipoAduana'].= '<Licencia>' . (Mage::helper('correos')->getValueConfig('paquetelicencia', 'paquete', $this->getStoreId())?'S':'N') . '</Licencia>';
                $data['tipoAduana'].= '<Certificado>' . (Mage::helper('correos')->getValueConfig('paquetecertificado', 'paquete', $this->getStoreId())?'S':'N') . '</Certificado>';
            }
            $data['tipoAduana'].= '</Aduana>';
            
        
        }

        return $data;
        
    }
    
    
    /**
     *
     */
    protected function getDataAduanaElements($order)
    {
        
        $data = '<DescAduanera>';
        foreach ($this->getAllItems() as $item) 
        {
            $data .= '<DATOSADUANA>';
            $data .= '<Cantidad>' . $item->getOrderItem()->getQtyShipped() . '</Cantidad>';
            if ($this->_goodsDua != '' && array_key_exists($this->_goodsDua, Mage::helper('correos/dua')->getTypeGoods()))
            {
                $data .= '<Descripcion>' . $this->_goodsDua . '</Descripcion>';
            } else {
                $data .= '<Descripcion>165</Descripcion>';
            }
            
            $pesoNeto = round($item->getOrderItem()->getWeight(), 0, PHP_ROUND_HALF_UP);
            $pesoNeto = Mage::helper('correos')->getFormatPesoPaquete($order->getStoreId(), $pesoNeto);
            
            $data .= '<Pesoneto>' . $pesoNeto . '</Pesoneto>';
            $data .= '<Valorneto>' . ($item->getOrderItem()->getPrice()*100) . '</Valorneto>';
            $data .= '</DATOSADUANA>';
        }
        $data .= '</DescAduanera>';
        
        return $data;
    }
    
    
/**
     *
     */
    protected function getDataHomepaq($dataBd)
    {
        
        $data['tipoHomepaq'] = '';
        if ($this->shippingMethod == 'homepaq48_homepaq48' || $this->shippingMethod == 'homepaq72_homepaq72')
        {
            $data['tipoHomepaq'] = '<CodigoHomepaq>' . $dataBd->getHomepaqId() . '</CodigoHomepaq>';
            $data['tipoHomepaq'].= '<ToquenIdCorPaq>' . $dataBd->getToken() . '</ToquenIdCorPaq>';
            $data['tipoHomepaq'].= '<AdmisionHomepaq>N</AdmisionHomepaq>';
        }

        return $data;
        
    }
    
    
    /**
     *
     */
    protected function checkMandatoryFields($data, $mandatory, $section)
    {
        foreach ($mandatory as $field)
        {
            if (!isset($data[$field]) || empty($data[$field]))
            {
                Mage::throwException(
                    Mage::helper('correos')->__('El campo %s de la sección %s es obligatorio para registrar el envío de un paquete.', $field, $section)
                );
            }
        }
        return true;
    }
    
    
    /**
     *
     */
    public function isInternationalOrder($order)
    {
        if (($order->getShippingAddress()->getCountryId() != 'ES' && $order->getShippingAddress()->getCountryId() != 'AD') || ($this->shippingMethod == 'correosinter_correosinter'))
        {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     */
    protected function preregistroCorreos($xmlSend, $tipoSolicitud)
    {

        $urlPeticion = Mage::helper('correos')->getValueConfig('gateway_preregistro', 'general', $this->getStoreId());              
        $datosAcceso = Mage::helper('correos')->getValueConfig('usercorreos', 'general', $this->getStoreId()).":".Mage::helper('correos')->getValueConfig('pwdcorreos', 'general', $this->getStoreId());
       
        try
        {
            $xmlResponse = Mage::getModel('correos/curl')->callCurl($datosAcceso, $urlPeticion, $xmlSend, $tipoSolicitud);
        } catch (Exception $e) {
            $xmlResponse = '';
            
        }        
        return $xmlResponse;
        
    }
    
    
    /**
     *
     */
    public function getEtiquetaRemote($order_id, $codEnvio)
    {
        try
        {
            /**
             *  pedido
             */
            $_order = Mage::getModel('sales/order')->load($order_id);
            if (!$_order) 
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No hay datos asociados a este pedido de Correos.')
                );
                return false;
            }
            if (!isset($this->_storeId)) 
            {
                $this->_storeId = $_order->getStoreId();
            }
            if (!in_array($_order->getShippingMethod(), Mage::helper('correos')->getAllowedMethods()))
            {
                return false;
            }
            

            /**
             *
             */
            $_helper = Mage::helper('correos/etiqueta');
            
            
            /**
             *  datos
             */
            $_data['fechaOperacion'] = date('d-m-Y H:i:s', strtotime($_order->getCreatedAt()));
            $_data['dataAuth'] = $this->getAuthXml();
            $_data['codEnvio'] = $codEnvio;

            
             
            /**
             *  xml
             */
            $xmlSend = $_helper->getXmlEtiqueta($_data);
            $peticionCorreos = $this->preregistroCorreos($xmlSend, 'SolicitudEtiquetaOp');
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
            if (!Mage::helper('correos')->checkAuthLoginResponse($peticionCorreos))
            {
                Mage::throwException(
                    Mage::helper('sales')->__('La pasarela de Correos ha devuelto un error de acceso. Compruebe sus credenciales.')
                );
                return false;
            }
            
            
            /**
             *
             */
            $dataXml = simplexml_load_string($peticionCorreos);
            if (!$dataXml->registerXPathNamespace('respuesta', 'http://www.correos.es/iris6/services/preregistroetiquetas'))
            {
                //  ha devuelto un error;
                Mage::throwException(
                    Mage::helper('correos')->__('Error en los datos devueltos por Correos.')
                );
                return false;
            }
            
            $_codExpedicion = $dataXml->xpath('//respuesta:CodExpedicion');
            $_codError = $dataXml->xpath('//respuesta:Resultado');
            
            
            if ($_codError[0] == '0')
            {
                // correcto
                $_datosBulto = $dataXml->xpath('//respuesta:Bulto');
                return $_datosBulto;
                
            } else {
                return false;
            }
            
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
    
    }
    
    
    /**
     *
     */
    public function getDocumentDua($order_id, $bultos = 1, $type = 'DCAF')
    {
        try
        {
            /**
             *  pedido
             */
            $_order = Mage::getModel('sales/order')->load($order_id);
            if (!$_order) 
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No hay datos asociados a este pedido de Correos.')
                );
                return false;
            }
            if (!in_array($_order->getShippingMethod(), Mage::helper('correos')->getAllowedMethods()))
            {
                return false;
            }
            

            /**
             *
             */
            $_helper = Mage::helper('correos/dua');
            
            
            /**
             *  datos
             */
            $_data['fechaOperacion'] = date('d-m-Y H:i:s', strtotime($_order->getCreatedAt()));
            $_data['dataAuth']  = $this->getAuthXml('prer:');
            $_data['NumeroEnvios'] = $bultos;
            $_data['type_doc'] = $type;
            
            
            if ($_order->getShippingMethod() == 'homepaq48_homepaq48' || $_order->getShippingMethod() == 'homepaq72_homepaq72')
            {
                $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $_order->getRealOrderId())->getFirstItem();
                $_infoPunto = Mage::getModel('correos/homepaq')->dataPunto($_dataRecogida['info_punto']);
                if (is_array($_infoPunto)) { 
                    $_data['Provincia'] = $_helper->getCodeRegion(trim($_infoPunto['region']));
                }
            } elseif ($_order->getShippingAddress()->getCountryId() == 'ES') {
                if (!$_helper->getCodeRegion($_order->getShippingAddress()->getRegion()))
                {
                    Mage::throwException(
                        Mage::helper('correos')->__('No se ha encontrado una provincia válida.')
                    );
                    return false;
                }
                $_data['Provincia'] = $_helper->getCodeRegion($_order->getShippingAddress()->getRegion());
            } else {
                $_data['Provincia'] = '';
            }
            $_data['PaisDestino']  = $_order->getShippingAddress()->getCountryId();
            $_data['NombreDestinatario']  = substr($_order->getShippingAddress()->getFirstname() . ' ' . $_order->getShippingAddress()->getLastname(), 0, 150);
            if (Mage::helper('correos')->getValueConfig('multisender', 'remitente', $this->getStoreId()))
            {
                $datosDestinatario = $this->getRemitenteArray();
                $_data['LocalidadFirma'] = $datosDestinatario['Localidad'];
            } else {
                $datosDestinatario = $this->getDataRemitente();
                $_data['LocalidadFirma'] = $datosDestinatario['Localidad'];
            }
             
            /**
             *  xml
             */
            $xmlSend = $_helper->getXmlDua($_data);
            $peticionCorreos = $this->preregistroCorreos($xmlSend, 'SolicitudDocumentacionAduaneraOp');
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
            if (!Mage::helper('correos')->checkAuthLoginResponse($peticionCorreos))
            {
                Mage::throwException(
                    Mage::helper('sales')->__('La pasarela de Correos ha devuelto un error de acceso. Compruebe sus credenciales.')
                );
                return false;
            }
            
            /**
             *
             */
            $dataXml = simplexml_load_string($peticionCorreos);
            
            if (!$dataXml->registerXPathNamespace('RespuestaSolicitudDocumentacionAduanera', 'http://www.correos.es/iris6/services/preregistroetiquetas'))
            {
                //  ha devuelto un error;
                Mage::throwException(
                    Mage::helper('correos')->__('Error en los datos devueltos por Correos.')
                );
                return false;
            }
            
            $_codEnvio = $dataXml->xpath('//RespuestaSolicitudDocumentacionAduanera:CodEnvio');
            $_codError = $dataXml->xpath('//RespuestaSolicitudDocumentacionAduanera:Resultado');

            
            if ($_codError[0] == '0')
            {

                // correcto
                $_datosDUA = $dataXml->xpath('//RespuestaSolicitudDocumentacionAduanera:Fichero');
                return $_datosDUA[0];
                
            } else {
                return false;
            }
            
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
    
    }
    
    
    /**
     *
     */
    public function getDocumentCn23cp71($order_id, $etiqueta)
    {
        try
        {
            /**
             *  pedido
             */
            $_order = Mage::getModel('sales/order')->load($order_id);
            if (!$_order) 
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No hay datos asociados a este pedido de Correos.')
                );
                return false;
            }
            if (!in_array($_order->getShippingMethod(), Mage::helper('correos')->getAllowedMethods()))
            {
                return false;
            }
            

            /**
             *
             */
            $_helper = Mage::helper('correos/dua');
            
             
            /**
             *  xml
             */
            $xmlSend = $_helper->getXmlCn23cp71($etiqueta);
            $peticionCorreos = $this->preregistroCorreos($xmlSend, 'SolicitudDocumentacionAduaneraCN23CP71');
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
            if (!Mage::helper('correos')->checkAuthLoginResponse($peticionCorreos))
            {
                Mage::throwException(
                    Mage::helper('sales')->__('La pasarela de Correos ha devuelto un error de acceso. Compruebe sus credenciales.')
                );
                return false;
            }
            
            /**
             *
             */
            $dataXml = simplexml_load_string($peticionCorreos);

            if (!$dataXml->registerXPathNamespace('RespuestaSolicitudDocumentacionAduaneraCN23CP71', 'http://www.correos.es/iris6/services/preregistroetiquetas'))
            {
                //  ha devuelto un error;
                Mage::throwException(
                    Mage::helper('correos')->__('Error en los datos devueltos por Correos.')
                );
                return false;
            }
            
            $_codError = $dataXml->xpath('//RespuestaSolicitudDocumentacionAduaneraCN23CP71:Resultado');

            if ($_codError[0] == '0')
            {

                // correcto
                $_datosCn23cp71 = $dataXml->xpath('//RespuestaSolicitudDocumentacionAduaneraCN23CP71:Fichero');
                return $_datosCn23cp71[0];
                
            } else {
                return false;
            }
            
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
    
    }
    
    
    
    /**
     *
     */
    public function logisticaInversa($_order, $downloadResponse = true)
    {
        
        try
        {
            
            $this->shippingMethod = $_order->getShippingMethod();
            
        
            /**
            *   informacion de recogida
            *   TO-DO: cuando no haya datos permitir finalizar el registro de forma normal aunque sea de Correos.
            */        
            $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $_order->getRealOrderId())->getFirstItem();
            if ($_dataRecogida->getId() == '') 
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No hay datos asociados a este pedido de Correos.')
                );
                return false;
            }
            
            
            /**
             *  No se puede realizar devoluciones para Paquete Internacional Prioritario
             */
            if ($this->isInternationalOrder($_order))
            {
                Mage::throwException(
                    Mage::helper('correos')->__('No se pueden realizar etiquetas de devolución para Paquete Internacional Prioritario.')
                );
                return false;
            }

        
            /**
             *
             */
            $_helper = Mage::helper('correos/preregistro');
            
            
            /**
             *  datos
             */
            $_data['fechaOperacion'] = date('d-m-Y H:i:s');
            $_data['dataAuth'] = $this->getAuthXml();
            if (Mage::helper('correos')->getValueConfig('multisender', 'remitente', $this->getStoreId()))
            {
                $_data['DatosDestinatario'] = $this->getRemitenteArray();
            } else {
                $_data['DatosDestinatario'] = $this->getDataRemitente();
            }
            $_data['datosRemitente'] = $this->getDataDestinatario($_order, $_dataRecogida->getMovilAsociado(), $_dataRecogida->getLanguage());
            $_data['datosRemitente']['Empresa'] = str_replace('<Empresa><![CDATA[', '', str_replace(']]></Empresa>', '', $_data['datosRemitente']['Empresa']));
            $_data['datosRemitente']['Personacontacto'] = str_replace('<PersonaContacto><![CDATA[', '', str_replace(']]></PersonaContacto>', '', $_data['datosRemitente']['Personacontacto']));
            $_data['DatosEnvio'] = $this->getDataEnvio($_order, $_dataRecogida);
            $_data['DatosEnvio']['CodProducto'] = 'S0148';  //  LOGÍSTICA INVERSA DE RETORNO
            $_data['DatosEnvio']['ModalidadEntrega']   = 'ST';
            $_data['DatosEnvio']['OficinaElegida']     = '';
            $_data['DatosDestinatario']['ZIP'] = substr(Mage::helper('correos')->getValueConfig('codpostal', 'remitente', $this->getStoreId()), 0, 5);
            $_data['DatosDestinatario']['Pais'] = substr(Mage::helper('correos')->getValueConfig('country_id', 'remitente', $this->getStoreId()), 0, 5);
            $_data['DatosDestinatario']['Idioma'] = 1;
            $_data['DatosDestinatario']['Empresa']     = '<Empresa><![CDATA[' . substr($_data['DatosDestinatario']['Empresa'], 0, 50) . ']]></Empresa>';
            $_data['DatosDestinatario']['PersonaContacto']     = '<PersonaContacto><![CDATA[' . substr($_data['DatosDestinatario']['Personacontacto'], 0, 50) . ']]></PersonaContacto>';
            $_data['DatosEnvio']['ValoresAnadidos']['Reembolso'] = '';
            
  

            /**
             *  xml
             */
            $xmlSend = $_helper->getXmlPreregistro($_data);

            

            $peticionCorreos = $this->preregistroCorreos($xmlSend, 'Preregistro');
            if ($peticionCorreos == '')
            {
                Mage::throwException(
                    Mage::helper('sales')->__('No se ha podido contactar con el servidor de Correos. Respuesta vac&iacute;a.')
                );
                return false;
            }
        


            /**
             *
             */
            if (!Mage::helper('correos')->checkAuthLoginResponse($peticionCorreos))
            {
                Mage::throwException(
                    Mage::helper('sales')->__('La pasarela de Correos ha devuelto un error de acceso. Compruebe sus credenciales.')
                );
                return false;
            }
        

            /**
             *
             */
            $dataXml = simplexml_load_string($peticionCorreos);
            if (!$dataXml->registerXPathNamespace('respuesta', 'http://www.correos.es/iris6/services/preregistroetiquetas'))
            {
                //  ha devuelto un error;
                Mage::throwException(
                    Mage::helper('correos')->__('Error en los datos devueltos por Correos.')
                );
                return false;
            }

            $_codExpedicion = $dataXml->xpath('//respuesta:CodExpedicion');
            $_codEnvio = $dataXml->xpath('//respuesta:Bulto');
            $_codError = $dataXml->xpath('//respuesta:Resultado');
            
            
            if ($_codError[0] == '0' && !$downloadResponse)
            {
                // correcto
                $_datosBulto = $dataXml->xpath('//respuesta:Bulto');
                return $_datosBulto;
                
            } elseif ($_codError[0] == '0') {
                
                // correcto
                $_datosBulto = $dataXml->xpath('//respuesta:Bulto');
                Mage::getSingleton('adminhtml/session')->addNotice('Se ha creado la etiqueta de retorno para Correos.');
                if (isset($_codExpedicion[0]))
                {
                    Mage::getSingleton('adminhtml/session')->addNotice('C&oacute;digo de expedici&oacute;n: ' . $_codExpedicion[0]);
                }
                Mage::getSingleton('adminhtml/session')->addNotice('C&oacute;digo de env&iacute;o: ' . $_codEnvio[0]->CodEnvio);
                
                
                /**
                 *  track
                 */                 
                $shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
                $shipment_collection->addAttributeToFilter('order_id', $_order->getId());
                 
                foreach($shipment_collection as $sc) {
                    $shipment = Mage::getModel('sales/order_shipment');
                    $shipment->load($sc->getId());
                    if($shipment->getId() != '') {
                        $track = Mage::getModel('sales/order_shipment_track')
                                 ->setShipment($shipment)
                                 ->setData('title', 'RMA Correos')
                                 ->setData('number', $_codEnvio[0]->CodEnvio)
                                 ->setData('carrier_code', 'correos_rma')
                                 ->setData('order_id', $shipment->getData('order_id'))
                                 ->save();
                    }
                }
                
                
                
                /**
                 *  correo de aviso
                 */
                $dataPdf = $this->getEtiquetaRemote($_order->getId(), $_codEnvio[0]->CodEnvio); 
                if (!$dataPdf)
                {
                    return;
                } else {


                    $ioAdapter  = new Varien_Io_File();
                    $path       = Mage::getBaseDir() . DS . 'var' . DS . 'pdf';
                    $file       = $path . DS . $dataPdf[0]->Etiqueta->Etiqueta_pdf->NombreF;
                    if (!$ioAdapter->fileExists($file)) 
                    {
                        if (!file_exists($path))
                        {
                            $importReadyDirResult = $ioAdapter->mkdir($path);
                            if (!$importReadyDirResult) 
                            {
                                Mage::throwException(Mage::helper('correos')->__('Directorio no accesible.'));
                            }
                        }
            
                        if (!$ioAdapter->write($file, base64_decode($dataPdf[0]->Etiqueta->Etiqueta_pdf->Fichero))) 
                        {
                            Mage::throwException(Mage::helper('correos')->__('No se ha podido guardar el contenido del PDF en el fichero temporal.'));
                        }
                    }



                    $storeId = Mage::app()->getStore()->getStoreId();
                    $transactionalEmail = Mage::getModel('core/email_template')
                                ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
                    $templateId = 'correos_logistica_inversa';
                    
                    $transactionalEmail
                        ->getMail()
                        ->createAttachment(
                            file_get_contents(Mage::getBaseDir() . DS . 'var' . DS . 'pdf' . DS . $dataPdf[0]->Etiqueta->Etiqueta_pdf->NombreF),
                            Zend_Mime::TYPE_OCTETSTREAM,
                            Zend_Mime::DISPOSITION_ATTACHMENT,
                            Zend_Mime::ENCODING_BASE64,
                            basename($dataPdf[0]->Etiqueta->Etiqueta_pdf->NombreF)
                        );
                    $transactionalEmail
                        ->sendTransactional($templateId, Mage::getStoreConfig('sales_email/order/identity', $storeId), $_order->getShippingAddress()->getEmail(), $_order->getShippingAddress()->getName() . ' ' . $_order->getShippingAddress()->getLastname(), '');

                }
                 
                                 
            } else {
                
                $_msjError = $dataXml->xpath('//respuesta:DescError');

                // error
                Mage::throwException(
                    Mage::helper('sales')->__('Error en la creaci&oacute;n de la operaci&oacute;n de preregistro. ' . $_msjError[0])
                );
                return false;
                
            }
            
            
            return true;
            
            
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
            
    }
    
    
    public function isEuropean($country)
    {
        if (in_array($country, $this->eupeanCountries)) return true;
        else return false;
    }


    public function getRemitenteArray()
    {
        $remitenteData = Mage::getModel('correos/remitente')->getCollection();
        if ($remitenteData)
        {
            $remitenteId =  $remitenteData->getFirstItem()->getId();
        }        
        if (Mage::helper('correos')->getValueConfig('multisender', 'remitente', $this->getStoreId()) && $remitenteId)
        {
            
            return $this->getDataMultiRemitente($remitenteId);
        } else {
            return $this->getDataRemitente();
        }
    }
}
