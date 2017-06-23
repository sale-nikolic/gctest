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
 * Greeting Card admin controller
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Company Inc.
 */
class MagentoTest_GreetingCard_Adminhtml_Greetingcard_GreetingcardController extends MagentoTest_GreetingCard_Controller_Adminhtml_GreetingCard
{
    /**
     * init the greeting card
     *
     * @access protected
     * @return MagentoTest_GreetingCard_Model_Greetingcard
     */
    protected function _initGreetingcard()
    {
        $greetingcardId  = (int) $this->getRequest()->getParam("id");
        $greetingcard    = Mage::getModel("magentotest_greetingcard/greetingcard");
        if ($greetingcardId) {
            $greetingcard->load($greetingcardId);
        }
        Mage::register("current_greetingcard", $greetingcard);
        return $greetingcard;
    }

    /**
     * Collects customers based on order value
     */
    public function collectAction() {
        // clear old values from database
        $collection = Mage::getModel("magentotest_greetingcard/greetingcard")->getCollection();
        foreach($collection as $item) {
            $item->delete();
        }
        $customerValue = array();
        $collection = Mage::getResourceModel('customer/customer_collection');
        $collection->joinTable(
            array('orders' => 'sales_flat_order'), 'customer_id=entity_id',
            array('base_grand_total' => 'base_grand_total')
        )
            ->getSelect()
            ->reset(Zend_DB_Select::COLUMNS)
            ->columns(array(
                'grand_total' => 'SUM(base_grand_total)',
                'email' => 'email',
                'stores' => 'GROUP_CONCAT(DISTINCT orders.store_id , ",")'))
            ->group('e.entity_id');
        foreach($collection as $customer){
            $customerValue[$customer->getEmail()] = array('total' => $customer->getGrandTotal(), 'stores' => $customer->getStores());
        }
        $write_adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        // save to database based on values
        foreach($customerValue as $email => $totalValue) {
            $reason = null;
            if($totalValue['total'] > 2000)
                $reason = 1;
            elseif($totalValue['total'] > 1000)
                $reason = 2;
            elseif($totalValue['total'] > 500)
                $reason = 3;
            if(!$reason)
                continue;
            //save card
            $item = Mage::getModel("magentotest_greetingcard/greetingcard");
            $item->setData("customer_email", $email);
            $item->setData("reason", $reason);
            $item->save();
            //save customer order stores
            $table  = Mage::getSingleton('core/resource')->getTableName('magentotest_greetingcard/greetingcard_store');
            $store = strtok($totalValue['stores'], ',');
            $data = [];
            while ($store != false) {
                $data[] = array(
                    'greetingcard_id'  => (int) $item->getId(),
                    'store_id' => (int) $store
                );
                $store = strtok(",");
            }
            if(!empty($data))
                $write_adapter->insertMultiple($table, $data);

        }

        $this->_redirect("*/*/");
    }

    /**
     * Send action
     */
    public function sendAction() {
        $collection = Mage::getModel("magentotest_greetingcard/greetingcard")->getCollection();
        foreach($collection as $item) {
            $mail = $this->_prepareEmail($item);
            try {
                $mail->send();
                $item->delete();
            } catch (Exception $error) {
                Mage::log($error->getMessage(), null, 'greeting_card_emails.log');
                continue;
            }
        }
        $this->_redirect("*/*/");
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper("magentotest_greetingcard")->__("Greeting Cards"))
             ->_title(Mage::helper("magentotest_greetingcard")->__("Greeting Cards"));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit greeting card - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function editAction()
    {
        $greetingcardId    = $this->getRequest()->getParam("id");
        $greetingcard      = $this->_initGreetingcard();
        if ($greetingcardId && !$greetingcard->getId()) {
            $this->_getSession()->addError(
                Mage::helper("magentotest_greetingcard")->__("This greeting card no longer exists.")
            );
            $this->_redirect("*/*/");
            return;
        }
        $data = Mage::getSingleton("adminhtml/session")->getGreetingcardData(true);
        if (!empty($data)) {
            $greetingcard->setData($data);
        }
        Mage::register("greetingcard_data", $greetingcard);
        $this->loadLayout();
        $this->_title(Mage::helper("magentotest_greetingcard")->__("Greeting Cards"))
             ->_title(Mage::helper("magentotest_greetingcard")->__("Greeting Cards"));
        if ($greetingcard->getId()) {
            $this->_title($greetingcard->getCustomerEmail());
        } else {
            $this->_title(Mage::helper("magentotest_greetingcard")->__("Add greeting card"));
        }
        if (Mage::getSingleton("cms/wysiwyg_config")->isEnabled()) {
            $this->getLayout()->getBlock("head")->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * send single greeting card - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function sendSingleAction()
    {
        $greetingcardId    = $this->getRequest()->getParam("id");
        $gc = Mage::getModel("magentotest_greetingcard/greetingcard")->load($greetingcardId);
        $mail = $this->_prepareEmail($gc);
        try {
            $mail->send();
            $gc->delete();
            Mage::getSingleton("adminhtml/session")->addSuccess(
                Mage::helper("magentotest_greetingcard")->__("Greeting card was successfully sent.", count($greetingcardIds))
            );
        } catch (Exception $error) {
            Mage::log($error->getMessage(), null, 'greeting_card_emails.log');
        }
        $this->_redirect("*/*/");
    }

    /**
     * new greeting card action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function newAction()
    {
        $this->_forward("edit");
    }

    /**
     * save greeting card - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost("greetingcard")) {
            try {
                $greetingcard = $this->_initGreetingcard();
                $greetingcard->addData($data);
                $greetingcard->save();
                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("magentotest_greetingcard")->__("Greeting Card was successfully saved")
                );
                Mage::getSingleton("adminhtml/session")->setFormData(false);
                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $greetingcard->getId()));
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setGreetingcardData($data);
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("magentotest_greetingcard")->__("There was a problem saving the greeting card.")
                );
                Mage::getSingleton("adminhtml/session")->setGreetingcardData($data);
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }
        }
        Mage::getSingleton("adminhtml/session")->addError(
            Mage::helper("magentotest_greetingcard")->__("Unable to find greeting card to save.")
        );
        $this->_redirect("*/*/");
    }

    /**
     * delete greeting card - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam("id") > 0) {
            try {
                $greetingcard = Mage::getModel("magentotest_greetingcard/greetingcard");
                $greetingcard->setId($this->getRequest()->getParam("id"))->delete();
                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("magentotest_greetingcard")->__("Greeting Card was successfully deleted.")
                );
                $this->_redirect("*/*/");
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("magentotest_greetingcard")->__("There was an error deleting greeting card.")
                );
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton("adminhtml/session")->addError(
            Mage::helper("magentotest_greetingcard")->__("Could not find greeting card to delete.")
        );
        $this->_redirect("*/*/");
    }

    /**
     * mass send greeting card - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function massSendAction()
    {
        $greetingcardIds = $this->getRequest()->getParam("greetingcard");
        if (!is_array($greetingcardIds)) {
            Mage::getSingleton("adminhtml/session")->addError(
                Mage::helper("magentotest_greetingcard")->__("Please select greeting cards to send.")
            );
        } else {
            try {
                foreach ($greetingcardIds as $greetingcardId) {
                    $gc = Mage::getModel("magentotest_greetingcard/greetingcard")->load($greetingcardId);
                    $mail = $this->_prepareEmail($gc);
                    try {
                        $mail->send();
                        $gc->delete();
                    } catch (Exception $error) {
                        Mage::log($error->getMessage(), null, 'greeting_card_emails.log');
                        continue;
                    }
                }
                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("magentotest_greetingcard")->__("Total of %d greeting cards were successfully sent.", count($greetingcardIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("magentotest_greetingcard")->__("There was an error sending greeting cards.")
                );
                Mage::logException($e);
            }
        }
        $this->_redirect("*/*/index");
    }

    /**
     * mass delete greeting card - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function massDeleteAction()
    {
        $greetingcardIds = $this->getRequest()->getParam("greetingcard");
        if (!is_array($greetingcardIds)) {
            Mage::getSingleton("adminhtml/session")->addError(
                Mage::helper("magentotest_greetingcard")->__("Please select greeting cards to delete.")
            );
        } else {
            try {
                foreach ($greetingcardIds as $greetingcardId) {
                    $greetingcard = Mage::getModel("magentotest_greetingcard/greetingcard");
                    $greetingcard->setId($greetingcardId)->delete();
                }
                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("magentotest_greetingcard")->__("Total of %d greeting cards were successfully deleted.", count($greetingcardIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("magentotest_greetingcard")->__("There was an error deleting greeting cards.")
                );
                Mage::logException($e);
            }
        }
        $this->_redirect("*/*/index");
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function massStatusAction()
    {
        $greetingcardIds = $this->getRequest()->getParam("greetingcard");
        if (!is_array($greetingcardIds)) {
            Mage::getSingleton("adminhtml/session")->addError(
                Mage::helper("magentotest_greetingcard")->__("Please select greeting cards.")
            );
        } else {
            try {
                foreach ($greetingcardIds as $greetingcardId) {
                $greetingcard = Mage::getSingleton("magentotest_greetingcard/greetingcard")->load($greetingcardId)
                            ->setStatus($this->getRequest()->getParam("status"))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__("Total of %d greeting cards were successfully updated.", count($greetingcardIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("magentotest_greetingcard")->__("There was an error updating greeting cards.")
                );
                Mage::logException($e);
            }
        }
        $this->_redirect("*/*/index");
    }

    /**
     * mass Reason change - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function massReasonAction()
    {
        $greetingcardIds = $this->getRequest()->getParam("greetingcard");
        if (!is_array($greetingcardIds)) {
            Mage::getSingleton("adminhtml/session")->addError(
                Mage::helper("magentotest_greetingcard")->__("Please select greeting cards.")
            );
        } else {
            try {
                foreach ($greetingcardIds as $greetingcardId) {
                $greetingcard = Mage::getSingleton("magentotest_greetingcard/greetingcard")->load($greetingcardId)
                    ->setReason($this->getRequest()->getParam("flag_reason"))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__("Total of %d greeting cards were successfully updated.", count($greetingcardIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError(
                    Mage::helper("magentotest_greetingcard")->__("There was an error updating greeting cards.")
                );
                Mage::logException($e);
            }
        }
        $this->_redirect("*/*/index");
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function exportCsvAction()
    {
        $fileName   = "greetingcard.csv";
        $content    = $this->getLayout()->createBlock("magentotest_greetingcard/adminhtml_greetingcard_grid")
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function exportExcelAction()
    {
        $fileName   = "greetingcard.xls";
        $content    = $this->getLayout()->createBlock("magentotest_greetingcard/adminhtml_greetingcard_grid")
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author Company Inc.
     */
    public function exportXmlAction()
    {
        $fileName   = "greetingcard.xml";
        $content    = $this->getLayout()->createBlock("magentotest_greetingcard/adminhtml_greetingcard_grid")
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author Company Inc.
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton("admin/session")->isAllowed("customer/magentotest_greetingcard/greetingcard");
    }

    /**
     * Prepare transactional email
     *
     * @param MagentoTest_GreetingCard_Model_Greetingcard
     * @access protected
     * @return Mage_Core_Model_Email
     * @author Company Inc.
     */
    protected function _prepareEmail($greetingCard)
    {
        //get default store id
        $website = array_values(Mage::app()->getWebsites())[0];
        $defStoreId = $website->getDefaultStore()->getId();

        $storeIds = $greetingCard->getStoreId();
        if(!empty($storeIds))
            $websiteId = Mage::getModel('core/store')->load($storeIds[0])->getWebsiteId();
        else
            $websiteId = Mage::getModel('core/store')->load($defStoreId)->getWebsiteId();
        $customer = Mage::getModel("customer/customer")->setWebsiteId($websiteId)->loadByEmail($greetingCard->getCustomerEmail());
        $email = $customer->getEmail();
        if(empty($email)) // in case of guest orders customer is not initialized
            $email = $greetingCard->getCustomerEmail();
        // get 'color' by reason number
        if($greetingCard->getReason() == 1)
            $color = '<span style="color:#E5E4E2"><b>platinum</b></span>';
        elseif($greetingCard->getReason() == 2)
            $color = '<span style="color:#D4AF37"><b>gold</b></span>';
        elseif($greetingCard->getReason() == 3)
            $color = '<span style="color:#C0C0C0"><b>silver</b></span>';
        $emailTemplate = Mage::getModel('core/email_template');
        $emailTemplate->loadByCode('greeting_card_email');
        $processedTemplate = $emailTemplate->getProcessedTemplate(array('name' => $customer->getFirstname(), 'color' => $color));
        $mail = Mage::getModel('core/email')
            ->setToName($customer->getFirstname())
            ->setToEmail($email)
            ->setFromEmail('noreply@example.com')
            ->setFromName("Example store")
            ->setBody($processedTemplate)
            ->setSubject($emailTemplate->getTemplateSubject())
            ->setType('html');
        return $mail;
    }
}
