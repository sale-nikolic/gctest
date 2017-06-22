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
        $customerValue = array();
        $orders = Mage::getModel("sales/order")->getCollection();
        foreach($orders as $order) {
            $order = $order->load($order->getId());
            $customer = Mage::getModel("customer/customer")->setWebsiteId(1)->loadByEmail($order->getCustomerEmail());
            $existsInArray = false;
            foreach($customerValue as $customerEmail => $totalValue) {
                if($customerEmail == $order->getCustomerEmail()) {
                    $existsInArray = true;
                }
            }
            if(!$existsInArray)
                $customerValue[$order->getCustomerEmail()] = 0;
            $customerValue[$customer->getEmail()] += $order->getGrandTotal();
        }

        // clear old values from database
        $collection = Mage::getModel("magentotest_greetingcard/greetingcard")->getCollection();
        foreach($collection as $item) {
            $item->delete();
        }
        // save to database based on values
        foreach($customerValue as $email => $totalValue) {
            $reason = null;
            if($totalValue > 2000)
                $reason = 1;
            if($totalValue > 1000)
                $reason = 2;
            if($totalValue > 500)
                $reason = 3;
            if(!$reason)
                continue;
            $item = Mage::getModel("magentotest_greetingcard/greetingcard");
            $item->setData("customer_email", $email);
            $item->setData("reason", $reason);
            $item->save();
        }

        $this->_redirect("*/*/");
    }

    /**
     * Send action
     */
    public function sendAction() {
        $collection = Mage::getModel("magentotest_greetingcard/greetingcard")->getCollection();

        foreach($collection as $item) {
            $selectedCustomerId = null;
            $customers = Mage::getModel("customer/customer")->getCollection();
            foreach($customers as $customer) {
                if($customer->getEmail() == $item->getCustomerEmail()) {
                    $selectedCustomerId = $customer->getId();
                }
            }
            if($selectedCustomerId) {
                $customer = Mage::getModel("customer/customer")->load($selectedCustomerId);
            }

            $item->delete();

            $mail = Mage::getModel('core/email');
            $mail->setToName($customer->getFirstname());
            $mail->setToEmail($customer->getEmail());
            $mail->setBody('Greetings from Example Store!\nThank you for being a great customer!');
            $mail->setSubject('Thank you for being a great customer!');
            $mail->setFromEmail('noreply@example.com');
            $mail->setFromName("Example store");
            $mail->setType('text');
            $mail->send();
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
}
