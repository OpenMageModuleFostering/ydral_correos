<?php 
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE  `{$this->getTable('correos_recoger_oficina')}` 
CHANGE `token` `token` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

");
$installer->endSetup();
