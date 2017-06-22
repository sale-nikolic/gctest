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
 * Admin search model
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Model_Adminhtml_Search_Greetingcard extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return MagentoTest_GreetingCard_Model_Adminhtml_Search_Greetingcard
     * @author Company Inc.
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('magentotest_greetingcard/greetingcard_collection')
            ->addFieldToFilter('customer_email', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $greetingcard) {
            $arr[] = array(
                'id'          => 'greetingcard/1/'.$greetingcard->getId(),
                'type'        => Mage::helper('magentotest_greetingcard')->__('Greeting Card'),
                'name'        => $greetingcard->getCustomerEmail(),
                'description' => $greetingcard->getCustomerEmail(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/greetingcard_greetingcard/edit',
                    array('id'=>$greetingcard->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
