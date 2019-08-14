<?php 
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('correos_remitente')}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `empresa` varchar(50) NOT NULL,
  `persona_contacto` varchar(50) NOT NULL,
  `pais` varchar(40) NOT NULL,
  `provincia` varchar(40) NOT NULL,
  `cp` varchar(10) NOT NULL,
  `localidad` varchar(50) NOT NULL,
  `direccion` varchar(50) NOT NULL,
  `telefono` varchar(12) NOT NULL,
  `email` varchar(50) NOT NULL,
  `telefono_movil` varchar(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


");
$installer->endSetup();