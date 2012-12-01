<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gordon
 * Date: 17.11.12
 * Time: 11:37
 * To change this template use File | Settings | File Templates.
 */
$installer = $this;
$installer->startSetup();

$version = Mage::getVersion();
if ($version < '1.6') {
    $installer->run("
    -- DROP TABLE IF EXISTS " . Lesti_Fpc_Model_Fpc::TABLE_FPC_URL . ";
    CREATE TABLE " . Lesti_Fpc_Model_Fpc::TABLE_FPC_URL . " (
      `url` varchar(255) NOT NULL,
      UNIQUE KEY `fpc_url` (`url`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
} else {
    $table = $installer->getConnection()
        ->newTable(Lesti_Fpc_Model_Fpc::TABLE_FPC_URL)
        ->addColumn('url', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false)
    )
        ->addIndex('UNQ_FPC_URL',
        array('url'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    );
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();