<?php

/** 
 *
 */
class Ydral_Correos_Adminhtml_PedidosController extends Mage_Adminhtml_Controller_Action 
{    
    
    /**
     *
     */
    public function exportarexcelAction()
    {
        
        try
        {
        
            $orderIds = $this->getRequest()->getPost('order_ids');
            if (!empty($orderIds))
            {
                
                /**
                 *
                 */
                $fileName  = 'pedidos.xls';
                $data = $this->getLayout()->createBlock('correos/adminhtml_exportar_pedidos')->getData($orderIds);

                $xmlObj = new Varien_Convert_Parser_Xml_Excel();
                $xmlObj->setVar('single_sheet', $fileName);
                $xmlObj->setData($data);
                $xmlObj->unparse();
                $content = $xmlObj->getData();
                             
                $this->_prepareDownloadResponse($fileName, $content);
                
                
                
            }
            
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('correos')->__('No se han encontrado pedidos para exportar.')
            );
            return false;
        }     
                	    
    }
    
    
    
    /**
     *
     */
    public function exportartxtAction()
    {
        
        try
        {
        
            $orderIds = $this->getRequest()->getPost('order_ids');
            if (!empty($orderIds))
            {
                
                /**
                 *
                 */
                $fileName  = 'pedidos.txt';
                $data = $this->getLayout()->createBlock('correos/adminhtml_exportar_pedidos')->getData($orderIds);

                $content = '';
                unset($data[0]);    //  En txt no se exporta cabecera
                foreach($data as $_data)
                {
                    array_shift($_data);
                    $_data = array_map('trim',$_data);
                    $content .= implode('|', $_data)."|\n";
                }
                
                $this->_prepareDownloadResponse($fileName, $content);
                
                
                
            }
            
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('correos')->__('No se han encontrado pedidos para exportar.')
            );
            return false;
        }     
                	    
    }
    
    
    
    
    /**
     *
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null)
    {
        
        $session = Mage::getSingleton('admin/session');
        if ($session->isFirstPageAfterLogin()) 
        {
            $this->_redirect($session->getUser()->getStartupPageUrl());
            return $this;
        }



        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/vnd.ms-excel; charset=utf-8', true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
            ->setHeader('Last-Modified', date('r'));

        if (!is_null($content)) {
            $this->getResponse()->setBody($content);
        }

        return $this;
    }
    

}