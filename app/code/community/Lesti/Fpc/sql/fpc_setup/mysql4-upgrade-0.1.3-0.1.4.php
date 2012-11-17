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

$installer->endSetup();