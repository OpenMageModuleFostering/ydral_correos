<?php

/** 
 *
 */
class Ydral_Correos_CitypaqController extends Mage_Checkout_Controller_Action 
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
    public function getstatesAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        
	    if ($this->getRequest()->isPost()) 
	    {
            $modelHomepaq = Mage::getModel('correos/homepaq');
            $storeId = Mage::app()->getStore()->getStoreId();
  	        $_htmlStates['states'] = $modelHomepaq->getStatesWithCitypaq($storeId); 
            $_tmpData = json_encode($_htmlStates);
    	        
    	    $this->getResponse()->setBody($_tmpData);
	    } else {
	        return;
	    }
    }
    
    
    /**
     *
     */
    public function getcitypaqsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        
	    if ($this->getRequest()->isPost()) 
	    {
            $modelHomepaq = Mage::getModel('correos/homepaq');
            $storeId = Mage::app()->getStore()->getStoreId();
  	        $_htmlCities['citypaqs'] = $modelHomepaq->getCitypaqs($this->getRequest()->getPost('searchby'), $this->getRequest()->getPost('searchvalue'), $storeId); 
            $_tmpData = json_encode($_htmlCities);
    	        
            $this->getResponse()->setBody($_tmpData);
	    } else {
	        return;
	    }
    }
    
    
    /**
     *
     */
    public function addfavoriteAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        
	    if ($this->getRequest()->isPost()) 
	    {
            $modelHomepaq = Mage::getModel('correos/homepaq');
            $storeId = Mage::app()->getStore()->getStoreId();
  	        $_urls = $modelHomepaq->getUrl('Add_Favorite', $this->getRequest()->getPost('user'), $this->getRequest()->getPost('favorite'), $storeId); 
            $_tmpData = json_encode($_urls);
    	        
            $this->getResponse()->setBody($_tmpData);
	    } else {
	        return;
	    }
    }
    
    
    /**
     *
     */
    public function finalfavoriteAction()
    {
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title></title></head><body onload="self.close()"></body></html>';
        $this->getResponse()->setBody($html);
        return;
    }
	
}