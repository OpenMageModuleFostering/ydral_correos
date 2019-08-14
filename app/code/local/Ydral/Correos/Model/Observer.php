<?php

/**
 *
 */
class Ydral_Correos_Model_Observer
{
    
    protected $_correosMail = 'ventaspaqueteria@correos.com';
    
    /**
     *
     */
    public function monitorCarriers($observer)
    {
        
        $_metodos   = Mage::helper('correos')->getTracksValidos();
        $_section   = $observer->getObject()->getSection();
        $_groups    = $observer->getObject()->getGroups();
        $_website   = $observer->getObject()->getWebsite();
        $_store     = $observer->getObject()->getStore();
        $_scope     = $observer->getObject()->getScope();
        $_scopeId   = $observer->getObject()->getScopeId();
        
        if ($_section != 'carriers')
        {
            return;
        }

        $_websiteId = (int)Mage::getConfig()->getNode('websites/' . $_website . '/system/website/id');
        $_storeId   = (int)Mage::getConfig()->getNode('stores/' . $_store . '/system/store/id');
        foreach ($_groups as $group => $groupData) 
        {
            if (!isset($groupData['fields']['active']['value'])) continue;
            
            if (in_array($group, $_metodos))
            {
                $_oldValue = Mage::getStoreConfig('carriers/' . $group . '/active', $_storeId);
                $_newValue = $groupData['fields']['active']['value'];
                
                if ($_oldValue != $_newValue)
                {
                    $this->_sendStatusMail($_groups, $group, $_newValue, $_storeId);
                }
                
            }
        }
        
    }
    
    /**
     *
     */
    private  function _sendStatusMail($groups, $group, $value, $storeId)
    {
        $emailTemplate  = Mage::getModel('core/email_template');
        $emailTemplate->loadDefault('correos_carriers_status');
        $emailTemplate->setTemplateSubject('MAGENTO, ACTIVACIÓN/DESACTIVACIÓN TRANSPORTISTA CORREOS');

        $salesData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
        $salesData['name'] = Mage::getStoreConfig('trans_email/ident_general/name'); 
        $emailTemplate->setSenderName($salesData['name']);
        $emailTemplate->setSenderEmail($salesData['email']);
        
        //  datos
        $emailTemplateVariables['codigo_etiquetador']   = Mage::helper('correos')->getValueConfig('etiquetador', 'general', $storeId);
        $emailTemplateVariables['numero_contrato']      = Mage::helper('correos')->getValueConfig('contrato', 'general', $storeId);
        $emailTemplateVariables['numero_cliente']       = Mage::helper('correos')->getValueConfig('numcliente', 'general', $storeId);
        $emailTemplateVariables['method']               = $group;
        $emailTemplateVariables['new_status']           = ($value == 1)?'Activado':'Desactivado';
        $emailTemplateVariables['date']                 = date('m-d-Y H:i:s');
        $_metodos   = Mage::helper('correos')->getTracksValidos();
        $_metodosActivos = array();
        foreach ($groups as $group => $groupData) 
        {
            if (!isset($groupData['fields']['active']['value'])) continue;
            if (in_array($group, $_metodos) && ($groupData['fields']['active']['value'] == '1'))
            {
                $_metodosActivos[] = $group;
            }    
        }
        $emailTemplateVariables['methods_activated']    = implode(', ', $_metodosActivos);
        $emailTemplateVariables['version']              = Mage::helper('correos')->getExtensionVersion();
        $emailTemplateVariables['url_store']            = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $emailTemplateVariables['name_store']           = Mage::app()->getStore($storeId)->getCode();
        
        try
        {
            $emailTemplate->send($this->_correosMail, 'Correos - Magento', $emailTemplateVariables);
        } catch (Exception $e) {            
            Mage::helper('correos')->_logger($e->getMessage());
        }
    }
    
    /**
     *
     */
    public function saveQuoteBefore($observer)
    {
        $quote      = $observer->getQuote();
        $params     = Mage::app()->getFrontController()->getRequest()->getPost();
        
        if(isset($params['oficinas_correos_content_select']))
        {
            $idSelector = trim(strip_tags($params['oficinas_correos_content_select']));
            $quote->setIdSelector($idSelector);
        }
        if(isset($params['cp_search']))
        {
            $cpField = trim(strip_tags($params['cp_search']));
            $quote->setCpSearch($cpField);
        }
        if(isset($params['phone_correos']))
        {
            $phoneField = addslashes(trim(strip_tags($params['phone_correos'])));
            $quote->setPhoneField($phoneField);
        }
        if(isset($params['correos_email']))
        {
            $emailField = addslashes(trim(strip_tags($params['correos_email'])));
            $quote->setEmailField($emailField);
        }
        if(isset($params['selectedpaq_code']))
        {
            $idHomepaq = Mage::helper('core')->escapeHtml(trim($params['selectedpaq_code']));
            $quote->setIdHomepaq($idHomepaq);
        }
        if(isset($params['homepaq_token']))
        {
            $token = Mage::helper('core')->escapeHtml(trim($params['homepaq_token']));
            $quote->setToken($token);
        }
        if(isset($params['selectedpaq_data']))
        {
            $info_punto = Mage::helper('core')->escapeHtml(trim($params['selectedpaq_data']));
            $quote->setInfoPunto($info_punto);
        }
        if(isset($params['correos_mobile_lang']))
        {
            $idioma_sms = Mage::helper('core')->escapeHtml(trim($params['correos_mobile_lang']));
            $quote->setIdiomaSms($idioma_sms);
        }
        if(isset($params['cr_timetable']))
        {
            $horarioField = addslashes(trim(strip_tags($params['cr_timetable'])));
            $quote->setHorarioField($horarioField);
        }
    }
    
    /**
     *
     */
    public function saveQuoteAfter($observer)
    {
        $quote      = $observer->getQuote();
        $idQuote    = $quote->getId();

        $model      = Mage::getModel('correos/recoger');
        
        $_dataTransaction = Mage::getModel('correos/recoger')
                    ->getCheckoutData('quote', $idQuote);
        
		$_dataSave = array(
		    'entity_type'       => 'quote',
		    'checkout_id'       => $idQuote,
		    'correos_recogida'  => $quote->getIdSelector(),
		    'correos_oficina'   => $quote->getCpSearch(),
		    'movil_asociado'    => $quote->getPhoneField(),
		    'email'             => $quote->getEmailField(),
		    'homepaq_id'        => $quote->getIdHomepaq(),
		    'token'             => $quote->getToken(),
		    'info_punto'        => $quote->getInfoPunto(),
		    'language'          => $quote->getIdiomaSms(),
		    'horario'           => $quote->getHorarioField(),
		    );
    		    
        if (count($_dataTransaction) == 0)
        {
            $quoteTransaction = Mage::getModel('correos/recoger')->setData($_dataSave);
            $quoteTransaction->save();
        } else {
            $_data = $_dataTransaction->getFirstItem();
            $idTransaction = $_data->getId();
            $quoteTransaction = Mage::getModel('correos/recoger')->load($idTransaction);
            $quoteTransaction->addData($_dataSave);
            /*           
            
            $quoteTransaction->setCorreosOficina($cpField);
            $quoteTransaction->setMovilAsociado($phoneField);
            $quoteTransaction->setEmail($emailField);
            $quoteTransaction->setHomepaqId($idHomepaq);
            $quoteTransaction->setToken($token);
            $quoteTransaction->setInfoPunto($info_punto);
            $quoteTransaction->setLanguage($idioma_sms);
            $quoteTransaction->setHorario($horarioField);
            */
            $quoteTransaction->save();
        }
    }
    
    /**
     *
     */
    public function loadQuoteAfter($observer)
    {
        $quote      = $observer->getQuote();
        $idQuote    = $quote->getId();
        $model      = Mage::getModel('correos/recoger');
        
        $_dataTransaction = Mage::getModel('correos/recoger')
                    ->getCheckoutData('quote', $idQuote);
        if (count($_dataTransaction) > 0)
        {
            $_data = $_dataTransaction->getFirstItem();
            $idTransaction = $_data->getId();
            $quoteTransaction = Mage::getModel('correos/recoger')->load($idTransaction);
            
            if ($quoteTransaction)
            {
                $data = $quoteTransaction->getData();
                foreach($data as $key => $value){
                    $quote->setData($key,$value);
                }
            }
        }
    }
    
    /**
     *
     */
    public function saveOrderAfter($observer)
    {
        $order      = $observer->getOrder();
        $idOrder    = $order->getIncrementId();
        $quote      = $observer->getQuote();
        $idQuote    = $quote->getId();
        $model      = Mage::getModel('correos/recoger');
        
        $_dataTransaction = Mage::getModel('correos/recoger')
                    ->getCheckoutData('quote', $idQuote);
           		    
        if (count($_dataTransaction) == 0)
        {
    		$_dataSave = array(
    		    'entity_type'       => 'order',
    		    'checkout_id'       => $idQuote,
    		    'correos_recogida'  => $quote->getIdSelector(),
    		    'correos_oficina'   => $quote->getCpSearch(),
    		    'movil_asociado'    => $quote->getPhoneField(),
    		    'email'             => $quote->getEmailField(),
    		    'homepaq_id'        => $quote->getIdHomepaq(),
    		    'token'             => $quote->getToken(),
    		    'info_punto'        => $quote->getInfoPunto(),
    		    'language'          => $quote->getIdiomaSms(),
    		    'horario'           => $quote->getHorarioField(),
    		    );
            
            $quoteTransaction = Mage::getModel('correos/recoger')->setData($_dataSave);
            $quoteTransaction->save();
        } else {
            $_data = $_dataTransaction->getFirstItem();
            $idTransaction = $_data->getId();
            $quoteTransaction = Mage::getModel('correos/recoger')->load($idTransaction);
            $quoteTransaction->setEntityType('order');
            $quoteTransaction->setCheckoutId($idOrder);
            $quoteTransaction->save();
        }
    }
    
    /**
     *
     */
    public function saveOrderBefore($observer)
    {
        $_order     = $observer->getEvent()->getOrder();
        $_quoteId   = $observer->getEvent()->getOrder()->getQuoteId();
        $_quote     = Mage::getModel('sales/quote')->load($_quoteId);
        
        $_shippingMethod = $_quote->getShippingAddress()->getShippingMethod();
        if (in_array($_shippingMethod, Mage::helper('correos')->getAllowedMethods()))
        {
            $_dataTransaction = Mage::getModel('correos/recoger')
                        ->getCheckoutData('quote', $_quoteId);
            if (count($_dataTransaction) > 0)
            {
                $_data = $_dataTransaction->getFirstItem();
                $idTransaction = $_data->getId();
                $quoteTransaction = Mage::getModel('correos/recoger')->load($idTransaction);
                
                if ($quoteTransaction)
                {
                    $_cp    = $quoteTransaction->getCorreosRecogida();
                    $_phone = $quoteTransaction->getMovilAsociado();
                    $_homepaq = $quoteTransaction->getHomepaqId();
                    $_email = $quoteTransaction->getEmail();
                    /**
                     *  Comprobaciones
                     */
                    if ( ($_shippingMethod == 'recogeroficina48_recogeroficina48' || $_shippingMethod == 'recogeroficina72_recogeroficina72') && (empty($_cp)) )
                    {
                        Mage::throwException(Mage::helper('correos')->__('No se ha especificado una oficina de Correos para la recogida.'));
                    }
                    if ( ($_shippingMethod == 'recogeroficina48_recogeroficina48' || $_shippingMethod == 'recogeroficina72_recogeroficina72') && (empty($_phone) && empty($_email)) )
                    {
                        Mage::throwException(Mage::helper('correos')->__('Debe indicar un teléfono móvil o email para poder realizar la entrega del envío por Correos.'));
                    } elseif ( ($_shippingMethod == 'recogeroficina48_recogeroficina48' || $_shippingMethod == 'recogeroficina72_recogeroficina72') && (!empty($_phone)) && (!Mage::helper('correos')->validarMovil($_phone)) ) {
                        Mage::throwException(Mage::helper('correos')->__('Número de teléfono móvil no válido en el envío por Correos.'));
                    }
                    if ( ($_shippingMethod == 'homepaq48_homepaq48' || $_shippingMethod == 'homepaq72_homepaq72') && (empty($_homepaq)) )
                    {
                        Mage::throwException(Mage::helper('correos')->__('Selecciona un Terminal CorreosPaq antes de continuar.'));
                    } else if ( ($_shippingMethod == 'homepaq48_homepaq48' || $_shippingMethod == 'homepaq72_homepaq72') && (!Mage::helper('correos')->validarMovil($_phone)) ) {
                        Mage::throwException(Mage::helper('correos')->__('Número de teléfono móvil no válido en el envío por Correos.'));
                    }
                }
            }
        }
    }
    
    /**
     *
     */
    public function loadOrderAfter($observer)
    {
        $order = $observer->getOrder();
        $idOrder    = $order->getIncrementId();
        $model      = Mage::getModel('correos/recoger');
        
        $_dataTransaction = Mage::getModel('correos/recoger')
                    ->getCheckoutData('order', $idOrder);
        if (count($_dataTransaction) > 0)
        {
            $_data = $_dataTransaction->getFirstItem();
            $idTransaction = $_data->getId();
            $orderTransaction = Mage::getModel('correos/recoger')->load($idTransaction);
            
            if ($orderTransaction)
            {
                $data = $orderTransaction->getData();
                foreach($data as $key => $value){
                    $order->setData($key,$value);
                }
            }
        }
    }
    
    
    /**
     *  Ocultamos COD en homepaq e internacional
     */
    public function paymentMethodIsActive(Varien_Event_Observer $observer) 
    {
        $event  = $observer->getEvent();
        $method = $event->getMethodInstance();
        $result = $event->getResult();
        $quote  = $event->getQuote();   
           
        //$result->isAvailable = true;
        $methodsNotAvailable = array ('correosinter_correosinter', 'homepaq48_homepaq48', 'homepaq72_homepaq72');
        
        if ($quote)
        {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            $isInternational = (($quote->getShippingAddress()->getCountryId() != 'ES' && $quote->getShippingAddress()->getCountryId() != 'AD') || ($shippingMethod == 'correosinter_correosinter'))?true:false;

            if ((in_array($shippingMethod, $methodsNotAvailable) || $isInternational) && Mage::helper('correos')->checkCashondelivery($method->getCode())) {
                $result->isAvailable = false;
            }
        }
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    /**
     *  DEPRECATED
     */
    public function setOficinaCorreosRecogidaOrderAdminhtml($observer)
    {

        $params = Mage::app()->getRequest()->getParams();
        if (isset($params['order']['shipping_method']))
        {
            $shippingMethod = $params['order']['shipping_method'];
        } else {
            return '';
        }
            


        /**
         *
         */
        $_cp = Mage::app()->getRequest()->getPost('oficinas_correos_content_select');
        $_phoneCorreos = Mage::app()->getRequest()->getPost('phone_correos');
        if ( ($shippingMethod == 'recogeroficina48_recogeroficina48' || $shippingMethod == 'recogeroficina72_recogeroficina72') && (empty($_cp)) )
        {
            return array('error' => -1, 'message' => Mage::helper('correos')->__('No se ha especificado una oficina de recogida.'));
        } else if ( ($shippingMethod == 'recogeroficina48_recogeroficina48' || $shippingMethod == 'recogeroficina72_recogeroficina72') && (!Mage::helper('correos')->validarMovil($_phoneCorreos)) ) {
            Mage::throwException(Mage::helper('correos')->__('Número de teléfono móvil no válido.'));
        }

        /**
         *
         */
        $_phone = Mage::app()->getRequest()->getPost('phone_correos');
        if ( ($shippingMethod == 'recogeroficina48_recogeroficina48' || $shippingMethod == 'recogeroficina72_recogeroficina72') && (empty($_phone)) )
        {
            Mage::throwException(Mage::helper('correos')->__('Debe indicar un teléfono móvil para poder realizar la entrega del envío.'));
        } elseif ( ($shippingMethod == 'recogeroficina48_recogeroficina48' || $shippingMethod == 'recogeroficina72_recogeroficina72') && (!Mage::helper('correos')->validarMovil($_phone)) ) {
            Mage::throwException(Mage::helper('correos')->__('Número de teléfono móvil no válido.'));
        }

        /**
         *
         */
        $_homepaq = Mage::app()->getRequest()->getPost('selectedpaq_code'); 
        $_phoneHomepaq = Mage::app()->getRequest()->getPost('phone_correos');
        if ( ($shippingMethod == 'homepaq48_homepaq48' || $shippingMethod == 'homepaq72_homepaq72') && (empty($_homepaq)) )
        {
            Mage::throwException(Mage::helper('correos')->__('Selecciona un Terminal CorreosPaq antes de continuar.'));
        } else if ( ($shippingMethod == 'homepaq48_homepaq48' || $shippingMethod == 'homepaq72_homepaq72') && (!Mage::helper('correos')->validarMovil($_phoneHomepaq)) ) {
            Mage::throwException(Mage::helper('correos')->__('Número de teléfono móvil no válido.'));
        }

     
     
        $order   = $observer->getOrder();
        $idQuote = $order->getQuoteId();
        $idOrder = $order->getIncrementId();
        $params  = Mage::app()->getFrontController()->getRequest();
        
        
        $idSelector = trim(strip_tags($params->getPost('oficinas_correos_content_select'))); 
        $cpField = addslashes(trim(strip_tags($params->getPost('cp_search'))));
        $phoneField = addslashes(trim(strip_tags($params->getPost('phone_correos'))));
        $emailField = addslashes(trim(strip_tags($params->getPost('correos_email'))));
        $idHomepaq  = Mage::helper('core')->escapeHtml(trim($params->getPost('selectedpaq_code')));
        $token      = Mage::helper('core')->escapeHtml(trim($params->getPost('homepaq_token')));
        $info_punto = Mage::helper('core')->escapeHtml(trim($params->getPost('selectedpaq_data')));
        $idioma_sms = Mage::helper('core')->escapeHtml(trim($params->getPost('correos_mobile_lang')));
        $horarioField = Mage::helper('core')->escapeHtml(trim($params->getPost('cr_timetable')));

        $_dataTransaction = Mage::getModel('correos/recoger')
                    ->getCheckoutData('quote', $idQuote);
            
        if (count($_dataTransaction) == 0)
        {
            
    		$_dataSave = array(
    		    'entity_type' => 'order',
    		    'checkout_id' => $idOrder,
    		    'correos_recogida' => $idSelector,
    		    'correos_oficina' => $cpField,
    		    'movil_asociado' => $phoneField,
    		    'email' => $emailField,
		        'homepaq_id' => $idHomepaq,
		        'token' => $token,
		        'info_punto' => $info_punto,
		        'language' => $idioma_sms,
		        'horario' => $horarioField,
    		    );
            
            $quoteTransaction = Mage::getModel('correos/recoger')->setData($_dataSave);
            $quoteTransaction->save();
            
        } else {
            
            $_data = $_dataTransaction->getFirstItem();
            $idTransaction = $_data->getId();
            $quoteTransaction = Mage::getModel('correos/recoger')->load($idTransaction);           
            $quoteTransaction->setEntityType('order');
            $quoteTransaction->setCheckoutId($idOrder);
            $quoteTransaction->save();
            
        }
        
    }

}