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
 * Greeting Card edit form tab
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 */
class MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Edit_Tab_Form
     * @author Company Inc.
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('greetingcard_');
        $form->setFieldNameSuffix('greetingcard');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'greetingcard_form',
            array('legend' => Mage::helper('magentotest_greetingcard')->__('Greeting Card'))
        );

        $fieldset->addField(
            'customer_email',
            'text',
            array(
                'label' => Mage::helper('magentotest_greetingcard')->__('Customer Email'),
                'name'  => 'customer_email',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'reason',
            'select',
            array(
                'label' => Mage::helper('magentotest_greetingcard')->__('Reason'),
                'name'  => 'reason',
                'required'  => true,
                'class' => 'required-entry',

                'values'=> Mage::getModel('magentotest_greetingcard/greetingcard_attribute_source_reason')->getAllOptions(true),
           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('magentotest_greetingcard')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('magentotest_greetingcard')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('magentotest_greetingcard')->__('Disabled'),
                    ),
                ),
            )
        );
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('current_greetingcard')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_greetingcard')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getGreetingcardData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getGreetingcardData());
            Mage::getSingleton('adminhtml/session')->setGreetingcardData(null);
        } elseif (Mage::registry('current_greetingcard')) {
            $formValues = array_merge($formValues, Mage::registry('current_greetingcard')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
