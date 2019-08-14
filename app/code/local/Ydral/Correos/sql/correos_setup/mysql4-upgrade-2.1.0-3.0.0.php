<?php 
$installer = $this;

//  Info
$statusTable        = $installer->getTable('sales/order_status');
$statusStateTable   = $installer->getTable('sales/order_status_state');


$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
        array('status' => 'processing_correos', 'label' => 'Procesando con Correos'),
    )
);
 
$installer->getConnection()->insertArray(
    $statusStateTable,
    array(
        'status',
        'state',
        'is_default'
    ),
    array(
        array(
            'status' => 'processing_correos',
            'state' => 'processing',
            'is_default' => 0
        )
    )
);


$installer->startSetup();

$installer->run("

ALTER TABLE  `{$this->getTable('correos_recoger_oficina')}` 
ADD  `language` VARCHAR(25) NOT NULL,
ADD  `email` VARCHAR(75) NOT NULL,
ADD  `horario` VARCHAR(5) NOT NULL;




CREATE TABLE IF NOT EXISTS `{$this->getTable('correos_registro')}` (
    `id` int(10) NOT NULL,
    `order_id` int(10) NOT NULL,
    `real_order_id` varchar(255) NOT NULL,
    `num_registro` varchar(100) NOT NULL,
    `peso` varchar(10) NOT NULL,
    `medida_ancho` varchar(10) NOT NULL,
    `medida_alto` varchar(10) NOT NULL,
    `medida_fondo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `{$this->getTable('correos_registro')}`
    ADD PRIMARY KEY (`id`), 
    ADD UNIQUE KEY `num_registro` (`num_registro`), 
    ADD KEY `order_id` (`order_id`);


CREATE TABLE IF NOT EXISTS `{$this->getTable('correos_entrega_horaria')}` (
    `id` int(10) NOT NULL,
    `cp` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('correos_registro')}`  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;


ALTER TABLE `{$this->getTable('correos_entrega_horaria')}`
    ADD PRIMARY KEY (`id`), 
    ADD KEY `cp` (`cp`);
    
ALTER TABLE `{$this->getTable('correos_entrega_horaria')}`  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;


");
$installer->endSetup();
