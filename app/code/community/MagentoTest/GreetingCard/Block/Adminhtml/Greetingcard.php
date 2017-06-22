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
 * Greeting Card admin block
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_greetingcard';
        $this->_blockGroup         = 'magentotest_greetingcard';
        parent::__construct();
        $this->_headerText         = Mage::helper('magentotest_greetingcard')->__('Greeting Card');
        $this->removeButton("add");

        $collectURL = Mage::helper("adminhtml")->getUrl("*/*/collect");
        $this->addButton("collect", array(
            "label" => Mage::helper('magentotest_greetingcard')->__('Collect Greeting Cards'),
                'onclick' => 'setLocation(\'' . $collectURL . '\')',
                'class'  => 'go'));

        $sendUrl = Mage::helper("adminhtml")->getUrl("*/*/send");
        $this->addButton("send", array("label" =>Mage::helper('magentotest_greetingcard')->__('Send Greeting Cards'),
            'onclick' => 'setLocation(\'' . $sendUrl . '\')',
            'class' => 'go'));
    }

}
