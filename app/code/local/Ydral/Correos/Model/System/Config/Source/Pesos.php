<?php

/**
 *
 */
class Ydral_Correos_Model_System_Config_Source_Pesos
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'gramos', 'label'=>Mage::helper('correos')->__('Gramos')),
            array('value'=>'kilos', 'label'=>Mage::helper('correos')->__('Kilos')),               
        );
    }

}