<?php

/**
 *
 */
class Ydral_Correos_Model_System_Config_Source_Provincias
{
    public function toOptionArray()
    {
        
        $collection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter('ES')
                ->load();
             
        $_provincias = array();   
        foreach($collection as $region) 
        {
            $_provincias[] = array('value' => $region->getCode(), 'label' => $region->getDefaultName());
        }

        return $_provincias;
        
    }

}