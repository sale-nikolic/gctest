<?php
/**
 * MagentoTest_GreetingCard extension
 * 
 * Magento Module for testing applicants.
 * 
 * @category       MagentoTest
 * @package        MagentoTest_GreetingCard
 * @copyright      Copyright (c) Anders Innovations Ltd
 */
/**
 * GreetingCard module upgrade script
 *
 * @category    MagentoTest
 * @package     MagentoTest_GreetingCard
 * @author      Anders Innovations Ltd
 */
$this->startSetup();

$greetingCardEmail = Mage::getModel('core/email_template');
$greetingCardEmail->setData('template_code', 'greeting_card_email');
$greetingCardEmail->setData('template_type', Mage_Core_Model_Email_Template::TYPE_HTML);
$greetingCardEmail->setData('template_subject', 'Thank you for being a great customer!');
$greetingCardEmail->setData('modified_at', Mage::getSingleton('core/date')->gmtDate());
$greetingCardEmail->setData('added_at', Mage::getSingleton('core/date')->gmtDate());
$greetingCardEmailContent = <<<'END_OF_EMAIL'
    Dear {{var name}},</br></br>
    Greetings from Example Store!</br>
    Thank you for being our {{var color}} customer!
END_OF_EMAIL;
$greetingCardEmail->setData('template_text', $greetingCardEmailContent);
$greetingCardEmail->save();

$this->endSetup();
