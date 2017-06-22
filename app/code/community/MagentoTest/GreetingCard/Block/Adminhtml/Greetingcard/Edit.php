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
 * Greeting Card admin edit form
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        parent::__construct();
        $this->_blockGroup = 'magentotest_greetingcard';
        $this->_controller = 'adminhtml_greetingcard';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('magentotest_greetingcard')->__('Save Greeting Card')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('magentotest_greetingcard')->__('Delete Greeting Card')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('magentotest_greetingcard')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author Company Inc.
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_greetingcard') && Mage::registry('current_greetingcard')->getId()) {
            return Mage::helper('magentotest_greetingcard')->__(
                "Edit Greeting Card '%s'",
                $this->escapeHtml(Mage::registry('current_greetingcard')->getCustomerEmail())
            );
        } else {
            return Mage::helper('magentotest_greetingcard')->__('Add Greeting Card');
        }
    }
}
