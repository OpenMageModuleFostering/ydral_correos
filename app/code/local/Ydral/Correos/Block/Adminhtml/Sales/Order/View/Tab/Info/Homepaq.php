<?php

/**
 *
 */
class Ydral_Correos_Block_Adminhtml_Sales_Order_View_Tab_Info_Homepaq extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    public function showInfo($_order)
    {
        $htmlTmp = '';
        $htmlTmp .= $this->helper('correos')->__('CorreosPaq');
        $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $_order->getRealOrderId());
        if (count($_dataRecogida))
        {
            $_data = $_dataRecogida->getFirstItem();
            $_punto = explode('|', $_data->getInfoPunto());
            
            $htmlTmp .= '<br /><br />' . $this->helper('correos')->__('Dirección de CorreosPaq de envío:') . ' <br />';
            if (isset($_punto[4]) && !empty($_punto[4])) $htmlTmp .= $_punto[4] . "<br />";
            $htmlTmp .= $_punto[0] . " - " . $_punto[1] . "<br />";
            $htmlTmp .= $_punto[2] . " - " . $_punto[3] . "<br />";
            $htmlTmp .= $this->helper('correos')->__('Código del terminal:') . ' ' . $_data->getHomepaqId() . "<br />";
            $htmlTmp .= $this->helper('correos')->__('Móvil asociado:') . ' ' . $_data->getMovilAsociado() . "<br />";
            
        }
        
        return $htmlTmp;
    }
}
