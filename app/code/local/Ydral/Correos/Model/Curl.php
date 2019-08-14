<?php

/** 
 *
 */
class Ydral_Correos_Model_Curl extends Mage_Core_Model_Abstract
{

    /**
     *
     */
    public function callCurl($datosAcceso, $urlPeticion, $xml, $tipoSolicitud = false)
    {
        
        try
        {
            
            $_configRequest = array(
                'timeout'   => 10,
                'header'    => false,
            );
            if (!empty($datosAcceso))
            {
                $_configRequest['userpwd'] = $datosAcceso;
            }
            
            
            $curl = new Varien_Http_Adapter_Curl();
            $curl->addOption(CURLOPT_FORBID_REUSE, true);
            $curl->addOption(CURLOPT_FRESH_CONNECT, true);
            //$curl->addOption(CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            $curl->setConfig($_configRequest);
            if (!$tipoSolicitud)
            {
                $curl->write('POST', $urlPeticion, '1.1', array("Content-Type: text/xml; charset=utf-8"), $xml);
            } else {
                $curl->write('POST', $urlPeticion, '1.1', array("Content-Type: text/xml; charset=utf-8", "SOAPAction: \"{$tipoSolicitud}\""), $xml);
            }
            $xmlResponse = $curl->read();
            $curl->close();
            
            $debugData['result'] = $xmlResponse;
            if ($curl->getError()) 
            { 
                $debugData['error'] = $curl->getError(); 
                $this->alertEmail();
                return false; 
            }

        } catch (Exception $e) {
                
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $this->alertEmail();
            $xmlResponse = '';
            
        }
        
        Mage::helper('correos')->_logger($debugData);
        Mage::helper('correos')->_logger($xmlResponse);
        
        return $xmlResponse;
        
    }
    
    /**
     *
     */
    protected function alertEmail()
    {
        $emailTemplate  = Mage::getModel('core/email_template');
        $emailTemplate->loadDefault('correos_ws_status');
        $emailTemplate->setTemplateSubject('INCIDENCIA TRANSPORTISTA MÓDULO CORREOS');

        $salesData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
        $salesData['name'] = Mage::getStoreConfig('trans_email/ident_general/name'); 
        $emailTemplate->setSenderName($salesData['name']);
        $emailTemplate->setSenderEmail($salesData['email']);
        
        $emailAlert = Mage::helper('correos')->getValueConfig('email_incidencia', 'opciones');
        
        try
        {
            if (!empty($emailAlert))
            {
                $emailTemplate->send($emailAlert, 'Correos - Magento', '');
            }
        } catch (Exception $e) {            
            Mage::helper('correos')->_logger($e->getMessage());
        }
    }
    
}
