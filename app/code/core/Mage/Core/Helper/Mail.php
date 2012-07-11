<?php

class Mage_Core_Helper_Mail extends Mage_Core_Helper_Abstract
{
    /**
     * Return a mailer instance to be used by the Mage_Core_Model_Email_Template
     *
     * The used factory helper and method can be configured at global/email/factory_helper.
     * Default is Mage_Core_Helper_Mail::getMailer.
     *
     * @return Zend_Mail
     */
    public function getMailer()
    {
        return new Zend_Mail('utf-8');
    }
}
