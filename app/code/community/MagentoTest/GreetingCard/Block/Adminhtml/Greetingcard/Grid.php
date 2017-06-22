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
 * Greeting Card admin grid block
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author Company Inc.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('greetingcardGrid');
        $this->setDefaultSort('customer_email');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Grid
     * @author Company Inc.
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('magentotest_greetingcard/greetingcard')
            ->getCollection();

        $customers = Mage::getModel("customer/customer")->getCollection();
        $emails = array();
        foreach($customers as $customer) {
            $c = $customer->load($customer->getId());
            $emails[] = $c->getEmail();
        }
        $collection->addFieldToFilter("customer_email", array("in" => $emails));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Grid
     * @author Company Inc.
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'customer_email',
            array(
                'header'    => Mage::helper('magentotest_greetingcard')->__('Customer Email'),
                'align'     => 'left',
                'index'     => 'customer_email',
            )
        );

        $this->addColumn(
            'reason',
            array(
                'header' => Mage::helper('magentotest_greetingcard')->__('Reason'),
                'index'  => 'reason',
                'type'  => 'options',
                'options' => Mage::helper('magentotest_greetingcard')->convertOptions(
                    Mage::getModel('magentotest_greetingcard/greetingcard_attribute_source_reason')->getAllOptions(false)
                )

            )
        );
        if (!Mage::app()->isSingleStoreMode() && !$this->_isExport) {
            $this->addColumn(
                'store_id',
                array(
                    'header'     => Mage::helper('magentotest_greetingcard')->__('Store Views'),
                    'index'      => 'store_id',
                    'type'       => 'store',
                    'store_all'  => true,
                    'store_view' => true,
                    'sortable'   => false,
                    'filter_condition_callback'=> array($this, '_filterStoreCondition'),
                )
            );
        }
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('magentotest_greetingcard')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('magentotest_greetingcard')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('magentotest_greetingcard')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('magentotest_greetingcard')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('magentotest_greetingcard')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Grid
     * @author Company Inc.
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('greetingcard');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('magentotest_greetingcard')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('magentotest_greetingcard')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('magentotest_greetingcard')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('magentotest_greetingcard')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('magentotest_greetingcard')->__('Enabled'),
                            '0' => Mage::helper('magentotest_greetingcard')->__('Disabled'),
                        )
                    )
                )
            )
        );
        $this->getMassactionBlock()->addItem(
            'reason',
            array(
                'label'      => Mage::helper('magentotest_greetingcard')->__('Change Reason'),
                'url'        => $this->getUrl('*/*/massReason', array('_current'=>true)),
                'additional' => array(
                    'flag_reason' => array(
                        'name'   => 'flag_reason',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('magentotest_greetingcard')->__('Reason'),
                        'values' => Mage::getModel('magentotest_greetingcard/greetingcard_attribute_source_reason')
                            ->getAllOptions(true),

                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param MagentoTest_GreetingCard_Model_Greetingcard
     * @return string
     * @author Company Inc.
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     * @author Company Inc.
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Grid
     * @author Company Inc.
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * filter store column
     *
     * @access protected
     * @param MagentoTest_GreetingCard_Model_Resource_Greetingcard_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return MagentoTest_GreetingCard_Block_Adminhtml_Greetingcard_Grid
     * @author Company Inc.
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
        return $this;
    }
}
