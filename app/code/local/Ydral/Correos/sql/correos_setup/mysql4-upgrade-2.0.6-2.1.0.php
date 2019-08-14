<?php 
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  `{$this->getTable('correos_recoger_oficina')}` 
ADD  `homepaq_id` VARCHAR(9) NOT NULL ,
ADD  `token` VARCHAR(50) NOT NULL ,
ADD  `info_punto` VARCHAR(255) NOT NULL ,
ADD INDEX (`homepaq_id`);

");
$installer->endSetup();