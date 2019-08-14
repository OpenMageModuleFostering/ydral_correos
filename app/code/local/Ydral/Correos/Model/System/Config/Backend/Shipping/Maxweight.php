<?php

/**
 *
 */
class Ydral_Correos_Model_System_Config_Backend_Shipping_Maxweight extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $weight = $this->getValue();
        
        if (!is_numeric($weight) || ($weight > 30) || ($weight < 0))
        {
            Mage::throwException("El valor máximo para el peso superior para el carrito es 30."); 
            return false;
        }
 
        return parent::save();
    }
}
