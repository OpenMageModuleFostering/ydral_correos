<?php 

/**
 *
 */
class Ydral_Correos_Block_System_Export_Abstract extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_method;


    /**
     *
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);              
        $params = array(
            'website' => $this->getRequest()->getParam('website')
        );

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Exportar CSV')
                    ->setOnClick('setLocation(\''.Mage::helper('adminhtml')->getUrl("*/adminhtml_export/exportTablerates", $params) . 'method/'.$this->_method.'/conditionName/\' + $(\'carriers_'.$this->_method.'_condition_name\').value + \'/tablerates.csv\' )')
                    ->toHtml();

        return $html;
    }

}