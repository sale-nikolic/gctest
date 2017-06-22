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
 * Greeting Card admin edit tabs
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Company Inc.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('greetingcard_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('magentotest_greetingcard')->__('Greeting Card'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Edit_Tabs
     * @author Company Inc.
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_greetingcard',
            array(
                'label'   => Mage::helper('magentotest_greetingcard')->__('Greeting Card'),
                'title'   => Mage::helper('magentotest_greetingcard')->__('Greeting Card'),
                'content' => $this->getLayout()->createBlock(
                    'magentotest_greetingcard/adminhtml_greetingcard_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_greetingcard',
                array(
                    'label'   => Mage::helper('magentotest_greetingcard')->__('Store views'),
                    'title'   => Mage::helper('magentotest_greetingcard')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'magentotest_greetingcard/adminhtml_greetingcard_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve greeting card entity
     *
     * @access public
     * @return MagentoTest_GreetingCard_Model_Greetingcard
     * @author Company Inc.
     */
    public function getGreetingcard()
    {
        return Mage::registry('current_greetingcard');
    }
}
