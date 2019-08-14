<?php

/** 
 *
 */
class Ydral_Correos_CheckoutController extends Mage_Checkout_Controller_Action 
{
    
    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if ($this->_isAdmin()) return false;

        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }
    
    /**
     * 
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
    
    /**
     *
     */
    protected function _isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')
        {
            return true;
        }
        
        if(isset($_SERVER['HTTP_REFERER'])) 
        {
            $urlReferer = $_SERVER["HTTP_REFERER"];
            if (strpos($urlReferer, 'adminhtml')) 
            {
                return true;
            }
            if (!strpos($urlReferer, 'admin/sales_order')) 
            {
                return false;
            }
            return true;
        }
        return false;
    }
    
    /**
     *
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }
    
    /**
     *
     */
	public function getdomicilioAction()
	{
        if ($this->_expireAjax()) {
            return;
        }
        $_metodo = $this->getRequest()->getPost('metodo');
        if (!$_metodo) return false;
	    $_html = $this->getLayout()
                    ->createBlock('correos/correos')
                    ->setData('metodo', $_metodo)
                    ->setTemplate('correos/domicilio.phtml')
                    ->toHtml();
        $this->getResponse()->setBody($_html);
	}
	
	/**
	 *
	 */
	public function gethomepaqAction()
	{
        if ($this->_expireAjax()) {
            return;
        }
        $_metodo = $this->getRequest()->getPost('metodo');
        if (!$_metodo) return false;
	    $_html = $this->getLayout()
                    ->createBlock('correos/correos')
                    ->setData('metodo', $_metodo)
                    ->setTemplate('correos/homepaq_init.phtml')
                    ->toHtml();
        $this->getResponse()->setBody($_html);
    }
    
    /**
     *
     */
    public function searchhomepaqAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        
	    if ($this->getRequest()->isPost()) 
	    {
            $modelHomepaq = Mage::getModel('correos/homepaq');
            $homepaqUser = $this->getRequest()->getPost('user');
            $_metodo = $this->getRequest()->getPost('metodo');
            $storeId = Mage::app()->getStore()->getStoreId();
            
	        $htmlOficinas = $modelHomepaq->getHtmlPuntos($homepaqUser, $storeId); 
	        $token = $modelHomepaq->getToken();	        
	        
	        $_html['code'] = $this->getLayout()
	                                ->createBlock('correos/correos')
	                                ->setData('metodo', $_metodo)
	                                ->setData('puntos', $htmlOficinas)
	                                ->setData('token', $token)
	                                ->setTemplate('correos/homepaq.phtml')
	                                ->toHtml();
	                                
            $_htmlOficinas['puntos'] = $htmlOficinas;
            $_token['token'] = array('value' => $token);
            $_tmpData = json_encode(array_merge($_htmlOficinas, $_token, $_html));	        
	        $this->getResponse()->setBody($_tmpData);
	        
        }
	}
	
	/**
	 *
	 */
	public function getdataAction()
	{
        if ($this->_expireAjax()) {
            return;
        }
	    
	    if ($this->getRequest()->isPost()) 
	    {
            
            /**
             *  comprobamos la peticion del codigo postal que se realiza
             */
            if ($this->getRequest()->getPost('codigoPostal') == '')
            {
                $codigoPostal = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getPostcode();
            } else {
                $codigoPostal = $this->getRequest()->getPost('codigoPostal');
            }
            
            /**
             *  verificamos el cambio de CP para oficina
             */
            if ($this->getRequest()->getPost('codigoPostal') != '')
	        {
	            $cpShipping = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getPostcode();
	            $cpAsk = $this->getRequest()->getPost('codigoPostal');
	            if ( $cpShipping && substr($cpShipping, 0, 2) != substr($cpAsk, 0, 2) ) 
	            {
	                $_message = Mage::helper('correos')->__('Si quiere enviar el pedido a una Oficina fuera de la provincia con la que se registró debe dar de alta nueva dirección.');
                    $this->showCodeError($_message);
                    return;
	            }
	        }
            
            /**
             *
             */
            $storeId = Mage::app()->getStore()->getStoreId();
	        if (!empty($codigoPostal))
	        {
    	        $_htmlOficinas['oficinas'] = Mage::getModel('correos/oficinas')->getHtmlOficinas($codigoPostal); 
    	        $_shippingAddress['shipping_address'] = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData();
    	        $_options['options'] = array('show_map' => Mage::helper('correos')->getValueConfig('showmap', 'opciones', $storeId));
    	        if (!isset($_shippingAddress['shipping_address']['telephone'])) { $_shippingAddress['shipping_address']['telephone'] = ''; }
    	        
    	        if ($_htmlOficinas['oficinas'] != false)
    	        {
    	            $_html['code'] = $this->getLayout()
    	                                ->createBlock('correos/correos')
    	                                ->setData('codpostal', $codigoPostal)
    	                                ->setData('metodo', $this->getRequest()->getPost('metodo'))
    	                                ->setTemplate('correos/oficina.phtml')
    	                                ->toHtml();
                } else {
                    $_html['code'] = Mage::helper('correos')->__('<div id="content_puntos_correos" style="margin-left: 15px; clear: both; ">Error al recuperar las oficinas disponibles.</div>');
                }
    	        
    	        $_tmpData = json_encode(array_merge($_htmlOficinas, $_shippingAddress, $_options, $_html));
    	        
    	        $this->getResponse()->setBody($_tmpData);
    	    } else {
    	        $_message = Mage::helper('correos')->__('Introduzca un código postal para localizar su oficina más cercana.');
                $this->showCodeError($_message);
                return;
    	    }
	        
	    }
	    
	}
	
	/**
	 *
	 */
	public function showCodeError($message)
	{
        $_htmlResponse = $this->getLayout()
                        ->createBlock('correos/correos')
                        ->setData('message', $message)
                        ->setTemplate('correos/error.phtml')
                        ->toHtml();
        $this->getResponse()->setBody($_htmlResponse);
        return;
	}
    
}