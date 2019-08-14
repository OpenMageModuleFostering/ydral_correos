<?php

/**
 * Adminhtml shipment items grid
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_Shipment_Create_Items extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Items
{
    /**
     *
     */
    public function shipWithCorreos()
    {
        if (in_array($this->getOrder()->getShippingMethod(), Mage::helper('correos')->getAllowedMethods())) return true;
        return false;
    }
    
    /**
     *
     */
    public function getOrderWeight()
    {
        $_order = $this->getOrder();
        $storeId = $_order->getStoreId();
        $peso = 0;
        foreach ($_order->getAllVisibleItems() as $item) 
        {
            $peso += $item->getWeight() * $item->getQtyOrdered();
        }
        if (Mage::helper('correos')->getValueConfig('peso', 'paquete', $storeId) == 'gramos') { $peso = $peso/1000; }
        $peso = intval($peso);
        if ($peso == 0) { $peso = 1; }  // peso minimo
        
        return $peso;
    }
    
    /**
     *
     */
    public function getMedidas()
    {
        $_order = $this->getOrder();
        if (strpos($_order->getShippingMethod(), 'homepaq') !== false) $medidas = true;
        else $medidas = false;
        
        return $medidas;
    }
    
    /**
     *
     */
    public function getOrigen($storeId)
    {
        return Mage::getModel('directory/region')->load(Mage::helper('correos')->getValueConfig('region_id', 'remitente', $storeId))->getName();
    }
    
    /**
     *
     */
    public function getDestino()
    {
        return $this->getOrder()->getShippingAddress()->getRegion();
    }

}
