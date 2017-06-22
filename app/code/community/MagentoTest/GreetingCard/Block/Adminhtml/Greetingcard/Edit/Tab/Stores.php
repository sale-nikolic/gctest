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
 * store selection tab
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Edit_Tab_Stores
     * @author Company Inc.
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('greetingcard');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'greetingcard_stores_form',
            array('legend' => Mage::helper('magentotest_greetingcard')->__('Store views'))
        );
        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('magentotest_greetingcard')->__('Store Views'),
                'title'    => Mage::helper('magentotest_greetingcard')->__('Store Views'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            )
        );
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
        $form->addValues(Mage::registry('current_greetingcard')->getData());
        return parent::_prepareForm();
    }
}
