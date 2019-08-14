<?php 

/**
 *
 */
class Ydral_Correos_Helper_Dua extends Mage_Core_Helper_Abstract
{
    
    protected $_allowedMethods = array (
            '01'     => 'alava',
            '02'     => 'albacete',
            '03'     => 'alicante',
            '04'     => 'almeria',
            '05'     => 'avila',
            '06'     => 'badajoz',
            '07'     => 'baleares',
            '08'     => 'barcelona',
            '09'     => 'burgos',
            '10'     => 'caceres',
            '11'     => 'cadiz',
            '12'     => 'castellon',
            '13'     => 'ciudad real',
            '14'     => 'cordoba',
            '15'     => 'a coruña',
            '16'     => 'cuenca',
            '17'     => 'girona',
            '18'     => 'granada',
            '19'     => 'guadalajara',
            '20'     => 'guipuzcoa',
            '21'     => 'huelva',
            '22'     => 'huesca',
            '23'     => 'jaen',
            '24'     => 'leon',
            '25'     => 'lleida',
            '26'     => 'la rioja',
            '27'     => 'lugo',
            '28'     => 'madrid',
            '29'     => 'malaga',
            '30'     => 'murcia',
            '31'     => 'navarra',
            '32'     => 'ourense',
            '33'     => 'asturias',
            '34'     => 'palencia',
            '35'     => 'las palmas',
            '36'     => 'pontevedra',
            '37'     => 'salamanca',
            '38'     => 'santa cruz de tenerife',
            '39'     => 'cantabria',
            '40'     => 'segovia',
            '41'     => 'sevilla',
            '42'     => 'soria',
            '43'     => 'tarragona',
            '44'     => 'teruel',
            '45'     => 'toledo',
            '46'     => 'valencia',
            '47'     => 'valladolid',
            '48'     => 'vizcaya',
            '49'     => 'zamora',
            '50'     => 'zaragoza',
            '51'     => 'ceuta',
            '52'     => 'melilla',
                                 );
                                 
                                 
    protected $_typeGoods = array (
            '24'     => 'Abonos de transportes',
            '25'     => 'Acciones',
            '26'     => 'Acero',
            '27'     => 'Alfombras',
            '28'     => 'Altavoces',
            '29'     => 'Aluminio',
            '30'     => 'Animales (vivos)',
            '31'     => 'Antigüedades',
            '32'     => 'Arcilla/Plastilina',
            '33'     => 'Artículos de cirugía',
            '34'     => 'Artículos de piel',
            '35'     => 'Artículos de plata',
            '36'     => 'Artículos de tocador',
            '37'     => 'Artículos deportivos',
            '38'     => 'Artículos médicos',
            '39'     => 'Atlas',
            '40'     => 'Automoción (componentes)',
            '41'     => 'Avión (componentes)',
            '42'     => 'Azulejos',
            '43'     => 'Bandeja',
            '44'     => 'Baterías',
            '45'     => 'Bebidas alcohólicas',
            '46'     => 'Billete de avión emitido',
            '47'     => 'Bolígrafos',
            '48'     => 'Bombillas',
            '49'     => 'Bonos, endorsados',
            '50'     => 'Botones',
            '51'     => 'Buscapersonas (beeper, Busca)',
            '52'     => 'Cabellos',
            '53'     => 'Calendarios',
            '54'     => 'Cámara',
            '55'     => 'Carpetas',
            '56'     => 'Cartas de navegación',
            '57'     => 'Cartuchos (tinta)',
            '58'     => 'Cartuchos de tóner',
            '59'     => 'Catálogo',
            '60'     => 'CD',
            '61'     => 'Certificados de nacimiento',
            '62'     => 'Cerveza',
            '63'     => 'Cheques (viajes)',
            '64'     => 'Cheques personales',
            '65'     => 'Cinta de video',
            '66'     => 'Cintas de audio',
            '67'     => 'Cintas, ordenadores',
            '68'     => 'Cojinetes de bolas',
            '69'     => 'Comida (no perecederos)',
            '70'     => 'Comida (perecederos)',
            '71'     => 'Componentes navales',
            '72'     => 'Cosméticos',
            '73'     => 'Cultivos',
            '74'     => 'Diamantes',
            '75'     => 'Diapositivas',
            '76'     => 'Dinero',
            '77'     => 'Discos',
            '78'     => 'Diskettes',
            '79'     => 'Dispositivos de CD/CD-RW',
            '80'     => 'Dispositivos de Diskettes',
            '81'     => 'Dispositivos de DVD/DVD-RW',
            '82'     => 'Documentos',
            '83'     => 'DVD',
            '84'     => 'Efectos personales',
            '85'     => 'Elementos luminosos',
            '86'     => 'Equipaje (personal)',
            '87'     => 'Equipo de Laboratorio',
            '88'     => 'Equipo de música',
            '89'     => 'Equipos eléctricos',
            '90'     => 'Equipos sin cables',
            '91'     => 'Esmeraldas',
            '92'     => 'Etiquetas de papel',
            '93'     => 'Etiquetas de tela',
            '94'     => 'Eventos (tickets, entradas)',
            '95'     => 'Facturas',
            '96'     => 'Fertilizantes',
            '97'     => 'Flores cortadas',
            '98'     => 'Folletos',
            '99'     => 'Fotocopias',
            '100'    => 'Fotografías',
            '101'    => 'Gafas',
            '102'    => 'Hardware informático',
            '103'    => 'Herbicidas',
            '104'    => 'Herramientas',
            '105'    => 'Hierbas',
            '106'    => 'Hierro',
            '107'    => 'Hilos',
            '108'    => 'Impresoras',
            '109'    => 'Instrumentos musicales',
            '110'    => 'Ipod',
            '111'    => 'Jarrón',
            '112'    => 'Joyería',
            '113'    => 'Juguetes',
            '114'    => 'Lápices',
            '115'    => 'Lectores, escáner',
            '116'    => 'Lentes de contacto',
            '117'    => 'Libros',
            '118'    => 'Lino',
            '119'    => 'Líquidos',
            '120'    => 'Madera',
            '121'    => 'Manuales',
            '122'    => 'Manuscritos',
            '123'    => 'Mapas',
            '124'    => 'Maquinaria (componente)',
            '125'    => 'Maquinaria (eléctrica)',
            '126'    => 'Material de oficina',
            '127'    => 'Material odontológico',
            '128'    => 'Medicamentos (con receta)',
            '129'    => 'Medicamentos (sin receta)',
            '130'    => 'Mercería',
            '131'    => 'Metales (no preciosos)',
            '132'    => 'Metales (preciosos)',
            '133'    => 'Micro Film',
            '134'    => 'Microficha',
            '135'    => 'Minerales',
            '136'    => 'Moneda de curso legal',
            '137'    => 'Monitores',
            '138'    => 'Moqueta',
            '139'    => 'Muebles',
            '140'    => 'Muestra de tejido',
            '141'    => 'Música (Partituras)',
            '142'    => 'Negativos',
            '143'    => 'Neumáticos',
            '144'    => 'Obras de arte ',
            '145'    => 'Ordenadores (componentes)',
            '146'    => 'Ordenadores personales',
            '147'    => 'Oro',
            '148'    => 'Pasaportes',
            '149'    => 'Película (comercial)',
            '150'    => 'Película (sin revelar)',
            '151'    => 'Peluca',
            '152'    => 'Perfumes',
            '153'    => 'Perlas',
            '154'    => 'Piedras',
            '155'    => 'Piedras (preciosas)',
            '156'    => 'Pieles',
            '157'    => 'Pilas',
            '158'    => 'Pinturas',
            '159'    => 'Pinturas técnicas',
            '160'    => 'Plantas',
            '161'    => 'Plata',
            '162'    => 'Platino',
            '163'    => 'Platos',
            '164'    => 'Plumas de aves',
            '165'    => 'Potpourri',
            '166'    => 'Productos de grafito',
            '167'    => 'Productos ecológicos',
            '168'    => 'Productos minerales',
            '169'    => 'Programas informáticos',
            '170'    => 'Publicaciones periódicas',
            '171'    => 'Publicidad',
            '172'    => 'Radios, AM/FM',
            '173'    => 'Ratón, ordenador',
            '174'    => 'Rayos X',
            '175'    => 'Relojes',
            '176'    => 'Reproductores audiovisuales',
            '177'    => 'Reproductores de CD',
            '178'    => 'Reproductores de DVD',
            '179'    => 'Reproductores de mp3',
            '180'    => 'Reproductores de VCR',
            '181'    => 'Revistas',
            '182'    => 'Ropa interior',
            '183'    => 'Ropa usada',
            '184'    => 'Ropa y Textil',
            '185'    => 'Router Wi-fi',
            '186'    => 'Rubíes',
            '187'    => 'Satélites (componentes)',
            '188'    => 'Sellos',
            '189'    => 'Semillas',
            '190'    => 'Software',
            '191'    => 'Souvenirs',
            '192'    => 'Suero',
            '193'    => 'Sustancias infecciosas',
            '194'    => 'Tabaco',
            '195'    => 'Talonario de cheques (blanco)',
            '196'    => 'Tarjetas de crédito',
            '197'    => 'Tarjetas de empresa',
            '198'    => 'Tarjetas de felicitación',
            '199'    => 'Tarjetas de identificación',
            '200'    => 'Tarjetas de teléfono',
            '201'    => 'Tarjetas electrónicas',
            '202'    => 'Teclados',
            '203'    => 'Tejidos y telas',
            '204'    => 'Teléfonos',
            '205'    => 'Teléfonos móviles',
            '206'    => 'Teléfonos satélites',
            '207'    => 'Televisores',
            '208'    => 'Tierra',
            '209'    => 'Tinta líquida',
            '210'    => 'Títulos al portador',
            '211'    => 'Títulos que no admiten portador',
            '212'    => 'Transparencias',
            '213'    => 'Utensilios de cocina',
            '214'    => 'Vajilla',
            '215'    => 'Vendaje',
            '216'    => 'Vestidos',
            '217'    => 'Videos',
            '218'    => 'Vino',
            '219'    => 'Visados',
            '220'    => 'Walkie Talkie',
            '221'    => 'Zafiros',
            '222'    => 'Zapatos',
                                );            
    
    /**
     *
     */
    public function getXmlDua(array $data)
    {
        
        if (empty($data)) return false;
        
        $xmlSend = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:prer="http://www.correos.es/iris6/services/preregistroetiquetas">
<soapenv:Header/>
<soapenv:Body>
<prer:SolicitudDocumentacionAduanera>
<prer:TipoESAD>{$data['type_doc']}</prer:TipoESAD>
{$data['dataAuth']}
<prer:Provincia>{$data['Provincia']}</prer:Provincia>
<prer:PaisDestino>{$data['PaisDestino']}</prer:PaisDestino>
<prer:NombreDestinatario>{$data['NombreDestinatario']}</prer:NombreDestinatario>
<prer:NumeroEnvios>{$data['NumeroEnvios']}</prer:NumeroEnvios>
<prer:LocalidadFirma>{$data['LocalidadFirma']}</prer:LocalidadFirma>
</prer:SolicitudDocumentacionAduanera>
</soapenv:Body>
</soapenv:Envelope>
XML;

        return $xmlSend;
        
    }
    
    
    /**
     *
     */
    public function getXmlCn23cp71($etiqueta)
    {
        
        if (empty($etiqueta)) return false;
        
        $xmlSend = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:prer="http://www.correos.es/iris6/services/preregistroetiquetas">
<soapenv:Header/>
<soapenv:Body>
<prer:SolicitudDocumentacionAduaneraCN23CP71>
<prer:codCertificado>{$etiqueta}</prer:codCertificado>
</prer:SolicitudDocumentacionAduaneraCN23CP71>
</soapenv:Body>
</soapenv:Envelope>
XML;

        return $xmlSend;
        
    }
    
    
    /**
     *
     */
    public function getCodeRegion($region)
    {
        if ($key = array_search(strtolower($region), $this->_allowedMethods))
        {
            return $key;
        } 
        return false;
    }
    
    /**
     *
     */
    public function getTypeGoods()
    {
        return $this->_typeGoods;
    }
    
    /**
     *
     */
    public function getProvinciasDua($storeId = '')
    {
        $_provincias = Mage::helper('correos')->getValueConfig('provincias', 'dua', $storeId);
        $_provincias = explode(',', $_provincias);
        return $_provincias;
    }
    
    /**
     *
     */
    public function isDua($order)
    {
        $shippingMethod = $order->getShippingMethod();
        $storeId        = $order->getStoreId();
        $_origen    = Mage::getModel('directory/region')->load(Mage::helper('correos')->getValueConfig('region_id', 'remitente', $storeId))->getName();
        $_destino   = $order->getShippingAddress()->getRegion();
        
        if ($shippingMethod == 'homepaq48_homepaq48' || $shippingMethod == 'homepaq72_homepaq72')
        {
            $_dataRecogida = Mage::getModel('correos/recoger')->getCheckoutData('order', $order->getRealOrderId())->getFirstItem();
            $_infoPunto = Mage::getModel('correos/homepaq')->dataPunto($_dataRecogida['info_punto']);
            if (is_array($_infoPunto)) { 
                $_destino = trim(strtolower($_infoPunto['region']));
            }
        }

        
        $isDua = false;
        $provinciasDua = Mage::helper('correos/dua')->getProvinciasDua($storeId);
        if (in_array($_origen, $provinciasDua))
        {
            $isDua = true;
        } elseif ($order->getShippingAddress()->getCountryId() != 'ES' && !Mage::getModel('correos/shipment')->isEuropean($order->getShippingAddress()->getCountryId())) { 
            $isDua = true;
        } elseif (in_array($_destino, $provinciasDua)) {
            $isDua = true;
        }
        
        return $isDua;
        
    }
    
    
    /**
     *
     */
    public function isCn23cp71($order)
    {
        $shippingMethod = $order->getShippingMethod();
        $storeId        = $order->getStoreId();
        $_origen    = Mage::helper('correos')->getValueConfig('country_id', 'remitente', $storeId);
        $_destino   = $order->getShippingAddress()->getCountryId();
        
        $isCn23cp71 = false;
        if ($shippingMethod == 'envio48_envio48' || $shippingMethod == 'envio72_envio72')
        {
            if ($_origen == 'AD' || $_destino == 'AD') { 
                $isCn23cp71 = true;
            } 
        } elseif ($shippingMethod == 'correosinter_correosinter') {
            $isCn23cp71 = true;
        }
        
        return $isCn23cp71;
        
    }
    
    
}