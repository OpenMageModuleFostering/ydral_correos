<?php 
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('correos_recoger_oficina')}` ADD `movil_asociado` VARCHAR( 10 ) NOT NULL;
");
$installer->endSetup();