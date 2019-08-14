<?php

/**
 *
 */
class Ydral_Correos_Block_System_Config_About 
    extends Mage_Adminhtml_Block_Abstract 
    implements Varien_Data_Form_Element_Renderer_Interface
{
    
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
	    
	    $_version = Mage::helper('correos')->getExtensionVersion();
	    
		$html = '<div style="margin-bottom:20px; padding:10px 5px 5px 5px; ">
		    <strong>' . Mage::helper('correos')->__('Versión del modulo: %s', $_version) . '</strong>
			</div>';
			
        return $html; 
        
    }
}
