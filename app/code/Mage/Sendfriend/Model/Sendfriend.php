<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sendfriend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * SendFriend Log
 *
 * @method Mage_Sendfriend_Model_Resource_Sendfriend _getResource()
 * @method Mage_Sendfriend_Model_Resource_Sendfriend getResource()
 * @method int getIp()
 * @method Mage_Sendfriend_Model_Sendfriend setIp(int $value)
 * @method int getTime()
 * @method Mage_Sendfriend_Model_Sendfriend setTime(int $value)
 *
 * @category    Mage
 * @package     Mage_Sendfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sendfriend_Model_Sendfriend extends Mage_Core_Model_Abstract
{
    /**
     * Recipient Names
     *
     * @var array
     */
    protected $_names   = array();

    /**
     * Recipient Emails
     *
     * @var array
     */
    protected $_emails  = array();

    /**
     * Sender data array
     *
     * @var array
     */
    protected $_sender  = array();

    /**
     * Product Instance
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Count of sent in last period
     *
     * @var int
     */
    protected $_sentCount;

    /**
     * Last values for Cookie
     *
     * @var string
     */
    protected $_lastCookieValue = array();

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Sendfriend_Model_Resource_Sendfriend');
    }

    /**
     * Retrieve Data Helper
     *
     * @return Mage_Sendfriend_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('Mage_Sendfriend_Helper_Data');
    }

    public function send()
    {
        if ($this->isExceedLimit()){
            Mage::throwException(Mage::helper('Mage_Sendfriend_Helper_Data')->__('You have exceeded limit of %d sends in an hour', $this->getMaxSendsToFriend()));
        }

        /* @var $translate Mage_Core_Model_Translate */
        $translate = Mage::getSingleton('Mage_Core_Model_Translate');
        $translate->setTranslateInline(false);

        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate = Mage::getModel('Mage_Core_Model_Email_Template');

        $message = nl2br(htmlspecialchars($this->getSender()->getMessage()));
        $sender  = array(
            'name'  => $this->_getHelper()->escapeHtml($this->getSender()->getName()),
            'email' => $this->_getHelper()->escapeHtml($this->getSender()->getEmail())
        );

        $mailTemplate->setDesignConfig(array(
            'area'  => Mage_Core_Model_App_Area::AREA_FRONTEND,
            'store' => Mage::app()->getStore()->getId()
        ));

        foreach ($this->getRecipients()->getEmails() as $k => $email) {
            $name = $this->getRecipients()->getNames($k);
            $mailTemplate->sendTransactional(
                $this->getTemplate(),
                $sender,
                $email,
                $name,
                array(
                    'name'          => $name,
                    'email'         => $email,
                    'product_name'  => $this->getProduct()->getName(),
                    'product_url'   => $this->getProduct()->getUrlInStore(),
                    'message'       => $message,
                    'sender_name'   => $sender['name'],
                    'sender_email'  => $sender['email'],
                    'product_image' => Mage::helper('Mage_Catalog_Helper_Image')->init($this->getProduct(),
                        'small_image')->resize(75),
                )
            );
        }

        $translate->setTranslateInline(true);
        $this->_incrementSentCount();

        return $this;
    }

    /**
     * Validate Form data
     *
     * @return bool|array
     */
    public function validate()
    {
        $errors = array();

        $name = $this->getSender()->getName();
        if (empty($name)) {
            $errors[] = Mage::helper('Mage_Sendfriend_Helper_Data')->__('The sender name cannot be empty.');
        }

        $email = $this->getSender()->getEmail();
        if (empty($email) OR !Zend_Validate::is($email, 'EmailAddress')) {
            $errors[] = Mage::helper('Mage_Sendfriend_Helper_Data')->__('Invalid sender email.');
        }

        $message = $this->getSender()->getMessage();
        if (empty($message)) {
            $errors[] = Mage::helper('Mage_Sendfriend_Helper_Data')->__('The message cannot be empty.');
        }

        if (!$this->getRecipients()->getEmails()) {
            $errors[] = Mage::helper('Mage_Sendfriend_Helper_Data')->__('At least one recipient must be specified.');
        }

        // validate recipients email addresses
        foreach ($this->getRecipients()->getEmails() as $email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $errors[] = Mage::helper('Mage_Sendfriend_Helper_Data')->__('An invalid email address for recipient was entered.');
                break;
            }
        }

        $maxRecipients = $this->getMaxRecipients();
        if (count($this->getRecipients()->getEmails()) > $maxRecipients) {
            $errors[] = Mage::helper('Mage_Sendfriend_Helper_Data')->__('No more than %d emails can be sent at a time.', $this->getMaxRecipients());
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }

    /**
     * Set cookie instance
     *
     * @param Mage_Core_Model_Cookie $product
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    public function setCookie($cookie)
    {
        return $this->setData('_cookie', $cookie);
    }

    /**
     * Retrieve Cookie instance
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        $cookie = $this->_getData('_cookie');
        if (!$cookie instanceof Mage_Core_Model_Cookie) {
            Mage::throwException(Mage::helper('Mage_Sendfriend_Helper_Data')->__('Please define a correct Cookie instance.'));
        }
        return $cookie;
    }

    /**
     * Set Visitor Remote Address
     *
     * @param int $ipAddr the IP address on Long Format
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    public function setRemoteAddr($ipAddr)
    {
        $this->setData('_remote_addr', $ipAddr);
        return $this;
    }

    /**
     * Retrieve Visitor Remote Address
     *
     * @return int
     */
    public function getRemoteAddr()
    {
        return $this->_getData('_remote_addr');
    }

    /**
     * Set Website Id
     *
     * @param int $id - website id
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    public function setWebsiteId($id)
    {
        $this->setData('_website_id', $id);
        return $this;
    }

    /**
     * Retrieve Website Id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->_getData('_website_id');
    }

    /**
     * Set Recipients
     *
     * @param array $recipients
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    public function setRecipients($recipients)
    {
        // validate array
        if (!is_array($recipients) OR !isset($recipients['email'])
            OR !isset($recipients['name']) OR !is_array($recipients['email'])
            OR !is_array($recipients['name'])) {
            return $this;
        }

        $emails = array();
        $names  = array();
        foreach ($recipients['email'] as $k => $email) {
            if (!isset($emails[$email]) && isset($recipients['name'][$k])) {
                $emails[$email] = true;
                $names[] = $recipients['name'][$k];
            }
        }

        if ($emails) {
            $emails = array_keys($emails);
        }

        return $this->setData('_recipients', new Varien_Object(array(
            'emails' => $emails,
            'names'  => $names
        )));
    }

    /**
     * Retrieve Recipients object
     *
     * @return Varien_Object
     */
    public function getRecipients()
    {
        $recipients = $this->_getData('_recipients');
        if (!$recipients instanceof Varien_Object) {
            $recipients =  new Varien_Object(array(
                'emails' => array(),
                'names'  => array()
            ));
            $this->setData('_recipients', $recipients);
        }
        return $recipients;
    }

    /**
     * Set product instance
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    public function setProduct($product)
    {
        return $this->setData('_product', $product);
    }

    /**
     * Retrieve Product instance
     *
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = $this->_getData('_product');
        if (!$product instanceof Mage_Catalog_Model_Product) {
            Mage::throwException(Mage::helper('Mage_Sendfriend_Helper_Data')->__('Please define a correct Product instance.'));
        }
        return $product;
    }

    /**
     * Set Sender Information array
     *
     * @param array $sender
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    public function setSender($sender)
    {
        if (!is_array($sender)) {
            Mage::helper('Mage_Sendfriend_Helper_Data')->__('Invalid Sender Information');
        }

        return $this->setData('_sender', new Varien_Object($sender));
    }

    /**
     * Retrieve Sender Information Object
     *
     * @throws Mage_Core_Exception
     * @return Varien_Object
     */
    public function getSender()
    {
        $sender = $this->_getData('_sender');
        if (!$sender instanceof Varien_Object) {
            Mage::throwException(Mage::helper('Mage_Sendfriend_Helper_Data')->__('Please define the correct Sender information.'));
        }
        return $sender;
    }

    /**
     * Get max allowed uses of "Send to Friend" function per hour
     *
     * @return integer
     */
    public function getMaxSendsToFriend()
    {
        return $this->_getHelper()->getMaxEmailPerPeriod();
    }

    /**
     * Get current Email "Send to friend" template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_getHelper()->getEmailTemplate();
    }

    /**
     * Get max allowed recipients for "Send to a Friend" function
     *
     * @return integer
     */
    public function getMaxRecipients()
    {
        return $this->_getHelper()->getMaxRecipients();
    }

    /**
     * Check if user is allowed to email product to a friend
     *
     * @return boolean
     */
    public function canEmailToFriend()
    {
        return $this->_getHelper()->isEnabled();
    }

    /**
     * Check if user is exceed limit
     *
     * @return boolean
     */
    public function isExceedLimit()
    {
        return $this->getSentCount() >= $this->getMaxSendsToFriend();
    }

    /**
     * Return count of sent in last period
     *
     * @param bool $useCache - flag, is allow to use value of attribute of model if it is processed last time
     * @return int
     */
    public function getSentCount($useCache = true)
    {
        if ($useCache && !is_null($this->_sentCount)) {
            return $this->_sentCount;
        }

        switch ($this->_getHelper()->getLimitBy()) {
            case Mage_Sendfriend_Helper_Data::CHECK_COOKIE:
                return $this->_sentCount = $this->_sentCountByCookies(false);
            case Mage_Sendfriend_Helper_Data::CHECK_IP:
                return $this->_sentCount = $this->_sentCountByIp(false);
            default:
                return 0;
        }
    }

    /**
     * Increase count of sent
     *
     * @return int
     */
    protected function _incrementSentCount()
    {
        switch ($this->_getHelper()->getLimitBy()) {
            case Mage_Sendfriend_Helper_Data::CHECK_COOKIE:
                return $this->_sentCount = $this->_sentCountByCookies(true);
            case Mage_Sendfriend_Helper_Data::CHECK_IP:
                return $this->_sentCount = $this->_sentCountByIp(true);
            default:
                return 0;
        }
    }

    /**
     * Return count of sent in last period by cookie
     *
     * @param bool $increment - flag, increase count before return value
     * @return int
     */
    protected function _sentCountByCookies($increment = false)
    {
        $cookie   = $this->_getHelper()->getCookieName();
        $time     = time();
        $newTimes = array();

        if (isset($this->_lastCookieValue[$cookie])) {
            $oldTimes = $this->_lastCookieValue[$cookie];
        } else {
            $oldTimes = $this->getCookie()->get($cookie);
        }

        if ($oldTimes) {
            $oldTimes = explode(',', $oldTimes);
            foreach ($oldTimes as $oldTime) {
                $periodTime = $time - $this->_getHelper()->getPeriod();
                if (is_numeric($oldTime) AND $oldTime >= $periodTime) {
                    $newTimes[] = $oldTime;
                }
            }
        }

        if ($increment) {
            $newTimes[] = $time;
            $newValue = implode(',', $newTimes);
            $this->getCookie()->set($cookie, $newValue);
            $this->_lastCookieValue[$cookie] = $newValue;
        }

        return count($newTimes);
    }
    /**
     * Return count of sent in last period by IP address
     *
     * @param bool $increment - flag, increase count before return value
     * @return int
     */
    protected function _sentCountByIp($increment = false)
    {
        $time   = time();
        $period = $this->_getHelper()->getPeriod();
        $websiteId = $this->getWebsiteId();

        if ($increment) {
            // delete expired logs
            $this->_getResource()->deleteLogsBefore($time - $period);
            // add new item
            $this->_getResource()->addSendItem($this->getRemoteAddr(), $time, $websiteId);
        }

        return $this->_getResource()->getSendCount($this, $this->getRemoteAddr(), time() - $period, $websiteId);
    }
    /**
     * Register self in global register with name send_to_friend_model
     *
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    public function register()
    {
        if (!Mage::registry('send_to_friend_model')) {
            Mage::register('send_to_friend_model', $this);
        }
        return $this;
    }
}
