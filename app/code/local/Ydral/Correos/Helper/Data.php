<?php 

/**
 *
 */
class Ydral_Correos_Helper_Data extends Mage_Core_Helper_Abstract
{


    protected $_allowedMethods = array (
                                 'correosinter_correosinter',
                                 'recogeroficina48_recogeroficina48',
                                 'recogeroficina72_recogeroficina72',
                                 'envio48_envio48',
                                 'envio72_envio72',
                                 'homepaq48_homepaq48',
                                 'homepaq72_homepaq72',
                                 );
                                 
                                 
    protected $_methodsName = array (
             'correosinter_correosinter'            => 'Paquete Postal Internacional Prioritario',
             'recogeroficina48_recogeroficina48'    => 'Paq 48 Oficina',
             'recogeroficina72_recogeroficina72'    => 'Paq 72 Oficina',
             'envio48_envio48'                      => 'Paq 48 Domicilio',
             'envio72_envio72'                      => 'Paq 72 Domicilio',
             'homepaq48_homepaq48'                  => 'Paq 48 entrega en HomePaq',
             'homepaq72_homepaq72'                  => 'Paq 72 entrega en HomePaq',
             );
                       
                       
    protected $_tracksValidos = array (
            'envio48', 
            'envio72', 
            'recogeroficina48', 
            'recogeroficina72', 
            'correosinter', 
            'homepaq48', 
            'homepaq72',
            );          
                                 

    /**
     *
     */
    public function getExtensionVersion()
	{
		return (string) Mage::getConfig()->getNode()->modules->Ydral_Correos->version;
	}


    /**
     *
     */
    public function validarMovil ($phone)
    {
        if(strlen(trim($phone)) == 0) return false;
        $tmp = substr($phone, 0, 1);
        if($tmp == 6 || $tmp == 7)
        {
            if(preg_match('/^[0-9]{9}$/', $phone))
                return true;
            else
                return false;
        } else {
            return false;
        }
    }


    /**
     *
     */
    public function getValueConfig ($data, $section, $storeId = '')
    {
        if (empty($storeId)) $storeId = Mage::app()->getStore()->getStoreId();
        return Mage::getStoreConfig('correos/' . $section . '/' . $data, $storeId);
    }
    
    
    /**
     *
     */
    public function getCarrierConfig ($data, $module, $storeId = '')
    {
        if (empty($storeId)) $storeId = Mage::app()->getStore()->getStoreId();
        return Mage::getStoreConfig('carriers/' . $module . '/' . $data, $storeId);
    }


	/**
	 *
	 */
	public function _logger ($msg)
	{
	    if ($this->getValueConfig('savelog', 'opciones'))
	    {
	        Mage::log($msg, null, 'correos.log');
	    }
	}


    /**
     *   comprueba que el xml sea correcto
     */
    public function is_valid_xml ($xml)
    {
        libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);
        
        $errors = libxml_get_errors();
        Mage::helper('correos')->_logger($errors);
        
        return empty($errors);
    }


    /**
     *
     */
    public function getAllowedMethods()
    {
        return $this->_allowedMethods;
    }
    
    
    /**
     *
     */
    public function checkAuthLoginResponse($html)
    {
        preg_match_all ("/<title>(.*)<\/title>/", $html, $title);
        if (isset($title[1][0]) && $title[1][0] == '401 Authorization Required')
        {
            return false;
        } else {
            return true;
        }
    }


    public function getMethods()
    {
        return $this->_methodsName;
    }   
    
    
    public function getTracksValidos()
    {
        return $this->_tracksValidos;
    }
    

    public function getCorreosError ($errorCode)
    {
        
        $_errorCodes = array (        
            '1' => 'El CP del destino no es v�lido',
            '2' => 'El cliente/contrato/anexo/tipo de franqueo no es v�lido',
            '3' => 'El producto no est� permitido para el pa�s indicado',
            '4' => 'No est� permitido el seguro',
            '5' => 'El seguro no puede ser 0',
            '6' => 'El importe del seguro no es correcto',
            '7' => 'No est� permitido el reembolso',
            '8' => 'El reembolso no puede ser 0',
            '9' => 'El importe del reembolso no es correcto',
            '10' => 'El N� de cuenta no es v�lido para un  Reembolso en Cuenta',
            '11' => 'El CP del remitente no es v�lido',
            '12' => 'El NIF del remitente no es v�lido',
            '13' => 'El NIF del destinatario no es v�lido',
            '14' => 'Faltan datos para el peso volum�trico',
            '15' => 'La suma de las dimensiones del paquete es mayor de 200 cm',
            '16' => 'El peso volum�trico indicado no es acorde con las dimensiones del paquete',
            '17' => 'La entrega exclusiva al destinatario no est� permitida',
            '18' => 'Es necesaria una descripci�n de la mercanc�a para este tipo de env�o',
            '19' => 'El valor declarado de la mercanc�a no puede ser 0',
            '20' => 'La oficina de destino no es v�lida',
            '21' => 'La direcci�n del remitente es obligatoria',
            '22' => 'No se ha podido recuperar la tarifa para este env�o',
            '23' => 'No existe la m�quina de franquear',
            '24' => 'La m�quina de franquear no pertenece a la misma Jefatura Pronvincial que la oficina',
            '25' => 'Insuficiencia de franqueo',
            '26' => 'El tipo de reembolso no puede estar vac�o',
            '27' => 'El N� de cuenta no es v�lido para un Reembolso en Cuenta',
            '28' => 'El tipo de env�o es obligatorio',
            '29' => 'Para este env�o debe introducir el n�mero de m�vil del destinatario',
            '30' => 'El Tel�fono del destinatario debe ser un m�vil v�lido',
            '31' => 'El tipo de franqueo no es v�lido',
            '32' => 'No se ha informado del CPI destino',
            '33' => 'El contrato/cliente no tiene anexo para este producto',
            '34' => 'No est� permitido el acuse de recibo',
            '35' => 'El peso no es v�lido',
            '36' => 'La direcci�n del destinatario es obligatoria',
            '37' => 'El c�digo de env�o ya est� pre-registrado en la aplicaci�n',
            '38' => 'El c�digo de env�o ya est� registrado en la aplicaci�n',
            '39' => 'El c�digo de env�o est� vac�o',
            '40' => 'Los �mbitos no coinciden',
            '41' => 'El nombre del destinatario est� vac�o',
            '42' => 'El nombre del remitente est� vac�o',
            '43' => 'Los apellidos del remitente est�n vac�os',
            '44' => 'Los apellidos del destinatario est�n vac�os',
            '45' => 'Error de formato en el mensaje',
            '46' => 'Error en IRIS6',
            '47' => 'Error en IRIS6',
            '50' => 'Error en el pre-registro',
            '51' => 'La versi�n del fichero no es v�lida',
            '52' => 'c�digo de certificado duplicado',
            '53' => 'El tipo de franqueo no es v�lido',
            '54' => 'No hay nombre ni empresa destinataria',
            '55' => 'Es obligatorio indicar la localidad de destino',
            '56' => 'El nombre del remitente o la empresa remitente es obligatorio',
            '57' => 'Es necesario indicar el tipo de reembolso',
            '58' => 'S�lo se permite un cliente detallable por fichero',
            '59' => 'Producto no v�lido',
            '60' => 'El c�digo de cliente no coincide con el del c�digo de producto',
            '61' => 'El c�digo de ceritificado no permite valores a�adidos (salvo acuse de recibo f�sico)',
            '62' => 'El d�gito de Control del c�digo de certificado es err�neo',
            '63' => 'Certificado duplicado en el propio fichero',
            '64' => 'El env�o ha sido anulado correctamente',
            '65' => 'El env�o ya est� dado de alta',
            '66' => 'El env�o ya est� dado de baja',
            '67' => 'El env�o no est� pre-registrado',
            '68' => 'El c�digo de est�do del env�o no es correcto',
            '69' => 'Un env�o prepagado no puede llevar VA',
            '70' => 'No se ha informado del importe del env�o',
            '71' => 'No se ha informado del importe de las promociones del env�o',
            '72' => 'La localidad del remitente es obligatoria',
            '73' => 'El c�digo etiquetador no es v�lido',
            '74' => 'El n�mero de manifiesto es obligatorio',
            '75' => 'Es necesario especificar una persona de contacto en la empresa destino',
            '76' => 'Es necesario especificar una persona de contacto en la empresa remitente',
            '77' => 'Lista de env�os vacia o no se han podido traducir los env�os',
            '78' => 'Faltan datos obligatorios',
            '79' => 'El producto y la modalidad de entrega son incompatibles',
            '80' => 'Es obligatorio informar del cliente/contrato',
            '81' => 'Es obligatorio informar del c�digo etiquedaror',
            '82' => 'Es obligatorio indicar la cantidad del primer art�culo contenido en el env�o',
            '83' => 'El c�digo etiquetador no existe en bbdd',
            '84' => 'Existe mas de un c�digo etiquetador en bbdd',
            '85' => 'Error al validar el c�digo etiquetador',
            '86' => 'Es obligatorio indicar si el env�o lleva asociada una factura superior a 500 euros',
            '87' => 'Es obligatorio indicar si desea que el DUA de exportaci�n sea tramitado por Correos',
            '88' => 'Es obligatorio informar del nombre o la empresa destinataria',
            '89' => 'Es obligatorio informar del nombre o la empresa remitente',
            '90' => 'El c�digo de certificado debe tener 13 o 23 caracteres',
            '91' => '�mbito no encontrado',
            '92' => 'El tipo de reembolso no es v�lido',
            '93' => 'La direcci�n del destinatario es incompleta',
            '94' => 'La direcci�n del remitente es incompleta',
            '95' => 'No se ha informado del importe antes de promociones',
            '96' => 'No se ha informado del impuesto antes de promociones',
            '97' => 'No se ha informado del importe despu�s de promociones',
            '98' => 'No se ha informado del impuesto despu�s de promociones',
            '99' => 'Falta informaci�n obligatoria para la admisi�n de un Reembolso en Apartado',
            '100' => 'El importe de Franqueo M�quina es insuficiente',
            '101' => 'Peso neto no informado',
            '102' => 'El c�digo de certificado de un env�o internacional debe tener 13 caracteres',
            '103' => 'El env�o se encuentra caducado y no puede ser admitido',
            '104' => 'El c�digo de certificado no es v�lido para ese tipo de producto',
            '105' => 'El n�mero de apartado no existe para la oficina indicada',
            '106' => 'Se debe indicar que hacer con el paquete en caso de no hacer la entrega',
            '107' => 'Se debe indicar una persona de contacto en la empresa del remitente',
            '108' => 'Se debe indicar la direcci�n de correo del destinatario',
            '109' => 'Faltan datos obligatorios para el paso de aduana',
            '110' => 'La suma de las dimensiones del paquete es mayor de 210 cm',
            '111' => 'Error al generar el c�digo de certificado con los datos recibidos',
            '112' => 'El cliente no est� autorizado a pre-registrar este env�o'
        );
        
        return $_errorCodes[$errorCode];

    }
    
    
    public function getTrackingPopupUrlBySalesModel($model)
    {
        
        $param = array(
            'hash' => Mage::helper('core')->urlEncode("order_id:{$model->getId()}:{$model->getProtectCode()}")
        );
        
        $storeId = is_object($model) ? $model->getStoreId() : null;
        $storeModel = Mage::app()->getStore($storeId);
        return $storeModel->getUrl('correos/tracking/popup', $param);
        
    }
    
    
    /**
     *  Codigo Postal dependiendo del pais
     */
    public function getCodigoPostal($order, $length = 5)
    {
        if ($order->getShippingAddress()->getCountryId() == 'PT')
        {
            return substr($order->getShippingAddress()->getPostcode(), 0, 4);
        } else {
            return substr($order->getShippingAddress()->getPostcode(), 0, $length);
        }
    }
    
    
    /**
     *  Peso del pedido
     *  var $formato: gr/kg
     */
    public function getPesoPaquete($order, $formato = 'gr', $qty = '')
    {
        $peso = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            if (!empty($qty))
            {
                $peso += $item->getWeight() * $item->getData($qty);
            } else {
                $peso += $item->getWeight() * $item->getQtyShipped();
            }
        }
        
        $storeId = $order->getStoreId();
        return $this->getFormatPesoPaquete($storeId, $peso, $formato);
        
    }
    
    
    /**
     *
     */
    public function getFormatPesoPaquete($storeId, $peso, $formato = 'gr')
    {
        if ($formato == 'gr')
        {
            if ($this->getValueConfig('peso', 'paquete', $storeId) == 'kilos') { $peso = $peso*1000; }   
            $peso = intval($peso);
        } else {
            if ($this->getValueConfig('peso', 'paquete', $storeId) != 'kilos') { $peso = $peso/1000; }
            $peso = intval($peso);
        }
        
        if ($peso == 0) { $peso = 1; }  // peso minimo
        return $peso;  
    }
    
    
    
    /**
     *
     */
    public function checkCashondelivery($code)
    {
        $_methodsCOD = array('cashondelivery', 'ig_cashondelivery', 'phoenix_cashondelivery', 'msp_cashondelivery', 'magegaga_cashondelivery');

        if (in_array($code, $_methodsCOD)) 
        {
            return true;
        }
        
        return false;
    }

}