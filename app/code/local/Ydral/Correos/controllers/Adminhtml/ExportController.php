<?php

/** 
 *
 */
class Ydral_Correos_Adminhtml_ExportController extends Mage_Adminhtml_Controller_Action 
{

    /**
     * Export shipping table rates in csv format
     *
     */
    public function exportTableratesAction()
    {
        
        $fileName   = 'tablerates.csv';
        
        if ($this->getRequest()->getParam('method'))
        {
            $methodName = $this->getRequest()->getParam('method');
        } else {
            $methodName = '';
        }
        /** @var $gridBlock Ydral_Correos_Block_Adminhtml_Shipping_Carrier_Tablerate_Grid */
        $gridBlock  = $this->getLayout()->createBlock('correos/adminhtml_shipping_carrier_tablerate_grid');
        $website    = Mage::app()->getWebsite($this->getRequest()->getParam('website'));
        if ($this->getRequest()->getParam('conditionName')) {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } else {
            $conditionName = $website->getConfig('carriers/tablerate/condition_name');
        }
        $gridBlock->setWebsiteId($website->getId())
            ->setConditionName($conditionName)
            ->setMethodName($methodName);
        $content = $gridBlock->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

}