<?php 

/**
 *
 */
class Ydral_Correos_Block_Correos extends Mage_Core_Block_Template
{
    
    protected $_shippingAdress;
    
    /**
     *
     */
    /*
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ydral/correos/correos.phtml');
    }
    */
    
    /**
     *
     */
    public function getShippingAddress()
    {
        if (empty($this->_shippingAdress))
        {
            $this->_shippingAdress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
        }
        return $this->_shippingAdress;
    }
    
    /**
     *
     */
    public function validarMovil()
    {
        $_address = $this->getShippingAddress();
        
        if (Mage::helper('correos')->validarMovil($_address->getTelephone()) && ( $_address->getCountryId() == 'ES' ))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     *
     */
    public function showTimetable($method)
    {
        $partsMethod = explode('_', $method); 
        $_metodo = $partsMethod[0];
        $_address = $this->getShippingAddress();
        if ($method == 'envio48_envio48' && ( $_address->getCountryId() == '' || $_address->getCountryId() == 'ES' ) && Mage::helper('correos')->getCarrierConfig('horario', $_metodo) && Mage::getModel('correos/entregahoraria')->checkCp($_address->getPostcode()))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     *
     */
    public function showAduanaMsg()
    {
        $_address = $this->getShippingAddress();
        $storeId  = Mage::app()->getStore()->getStoreId();
        if ((Mage::getModel('correos/shipment')->isEuropean($_address->getCountryId()) && ($_address->getCountryId() != 'PT')) || in_array($_address->getRegion(), Mage::helper('correos/dua')->getProvinciasDua($storeId)))
        {
            return true;
        }
        
        return false;
    }
    
    /**
     *
     */
    public function getHtmlOficinas($cp)
    {
        return Mage::getModel('correos/oficinas')->getHtmlOficinas($cp); 
    }
}