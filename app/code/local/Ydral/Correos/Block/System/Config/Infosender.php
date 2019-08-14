<?php

/**
 *
 */
class Ydral_Correos_Block_System_Config_Infosender 
    extends Mage_Adminhtml_Block_Abstract 
    implements Varien_Data_Form_Element_Renderer_Interface
{
    
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
	    
		$html = '<div style="margin-bottom:20px; padding:10px 5px 5px 5px; ">' . 
		    Mage::helper('correos')->__('El sistema de multiremitente le permite, durante el proceso de envío, seleccionar el remitente de su paquete de Correos.') .
		    '<br />' .
		    Mage::helper('correos')->__('Si solo va a utilizar un único remitente, no seleccione la opción de multiremitente y rellene los datos generales de remitente.') .
		    '<br />' .
		    Mage::helper('correos')->__('Si va a utilizar el sistema multiremitente debe subir un fichero en formato CSV con la siguiente estructura:') .
		    '<br />' .
		    '<i>' . Mage::helper('correos')->__('Nombre,Apellidos,DNI,Empresa,Persona_de_contacto,País,Provincia,CP,Localidad,Dirección,Teléfono,Email,Teléfono_Móvil') . '</i>' .
		    '<br />' .
		    Mage::helper('correos')->__('Cada línea del fichero corresponde con una dirección de remitente.') .
			'</div>';
			
        return $html; 
        
    }
}
