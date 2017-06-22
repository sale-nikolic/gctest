<?php
/**
 * MagentoTest_GreetingCard extension
 * 
 * Magento Module for testing applicants.
 * 
 * @category       MagentoTest
 * @package        MagentoTest_GreetingCard
 * @copyright      Copyright (c) Company Inc.
 */
/**
 * Greeting Card model
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Model_Greetingcard extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'magentotest_greetingcard_greetingcard';
    const CACHE_TAG = 'magentotest_greetingcard_greetingcard';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'magentotest_greetingcard_greetingcard';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'greetingcard';

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('magentotest_greetingcard/greetingcard');
    }

    /**
     * before save greeting card
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Model_Greetingcard
     * @author Company Inc.
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save greeting card relation
     *
     * @access public
     * @return MagentoTest_GreetingCard_Model_Greetingcard
     * @author Company Inc.
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author Company Inc.
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        $values['reason'] = '4';

        return $values;
    }
    
}
