<?php
/**
 * MagentoTest_GreetingCard extension
 * 
 * Magento Module for testing applicants.
 * 
 * @category       MagentoTest
 * @package        MagentoTest_GreetingCard
 * @copyright      Copyright (c) Anders Innovations Ltd
 */
/**
 * GreetingCard module install script
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Anders Innovations Ltd
 */
$this->startSetup();

$table = $this->getConnection()
    ->newTable($this->getTable('magentotest_greetingcard/greetingcard'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('identity'  => true, 'nullable'  => false, 'primary'   => true,), 'Greeting Card ID')
    ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable'  => false),'Customer Email')
    ->addColumn('reason', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable'  => false), 'Reason')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(), 'Enabled')    
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Greeting Card Modification Time')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Greeting Card Creation Time') 
    ->setComment('Greeting Card Table');
$this->getConnection()->createTable($table);

$table = $this->getConnection()
    ->newTable($this->getTable('magentotest_greetingcard/greetingcard_store'))
    ->addColumn('greetingcard_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array('nullable'  => false, 'primary'   => true,), 'Greeting Card ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array('unsigned'  => true, 'nullable'  => false, 'primary'   => true,), 'Store ID')
    ->addIndex($this->getIdxName('magentotest_greetingcard/greetingcard_store', array('store_id')), array('store_id'))
    ->addForeignKey($this->getFkName('magentotest_greetingcard/greetingcard_store', 'greetingcard_id', 'magentotest_greetingcard/greetingcard', 'entity_id'), 'greetingcard_id', $this->getTable('magentotest_greetingcard/greetingcard'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($this->getFkName('magentotest_greetingcard/greetingcard_store', 'store_id', 'core/store', 'store_id'), 'store_id', $this->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Greeting Cards To Store Linkage Table');
$this->getConnection()->createTable($table);

$this->endSetup();
