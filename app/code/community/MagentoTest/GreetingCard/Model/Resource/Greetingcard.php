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
 * Greeting Card resource model
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Anders Innovations Ltd
 */
class MagentoTest_GreetingCard_Model_Resource_Greetingcard extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     * @author Anders Innovations Ltd
     */
    public function _construct()
    {
        $this->_init('magentotest_greetingcard/greetingcard', 'entity_id');
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @access public
     * @param int $greetingcardId
     * @return array
     * @author Anders Innovations Ltd
     */
    public function lookupStoreIds($greetingcardId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('magentotest_greetingcard/greetingcard_store'), 'store_id')
            ->where('greetingcard_id = ?', (int)$greetingcardId);
        return $adapter->fetchCol($select);
    }

    /**
     * Perform operations after object load
     *
     * @access public
     * @param Mage_Core_Model_Abstract $object
     * @return MagentoTest_GreetingCard_Model_Resource_Greetingcard
     * @author Anders Innovations Ltd
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param MagentoTest_GreetingCard_Model_Greetingcard $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('greetingcard_greetingcard_store' => $this->getTable('magentotest_greetingcard/greetingcard_store')),
                $this->getMainTable() . '.entity_id = greetingcard_greetingcard_store.greetingcard_id',
                array()
            )
            ->where('greetingcard_greetingcard_store.store_id IN (?)', $storeIds)
            ->order('greetingcard_greetingcard_store.store_id DESC')
            ->limit(1);
        }
        return $select;
    }

    /**
     * Assign greeting card to store views
     *
     * @access protected
     * @param Mage_Core_Model_Abstract $object
     * @return MagentoTest_GreetingCard_Model_Resource_Greetingcard
     * @author Anders Innovations Ltd
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('magentotest_greetingcard/greetingcard_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = array(
                'greetingcard_id = ?' => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );
            $this->_getWriteAdapter()->delete($table, $where);
        }
        if ($insert) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'greetingcard_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }
}
