<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_View_Tab_Info_Envio extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    
    protected $_description;
    
    public function showInfo($_order)
    {
        $htmlTmp = '';
        $htmlTmp .= $this->helper('correos')->__($this->_description);
        
        $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $_order->getRealOrderId());
        if (count($_dataRecogida))
        {
            $_data = $_dataRecogida->getFirstItem();
            if ($_data && $_data->getMovilAsociado())
            {
                $htmlTmp .= '<br /><br />';
                $htmlTmp .= $this->helper('correos')->__('Móvil asociado:') . ' ' . $_data->getMovilAsociado() . "<br />";
            }
        }
        
        return $htmlTmp;
    }
}
