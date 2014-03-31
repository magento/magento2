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
 * @category    Magento
 * @package     Magento_Sendfriend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sendfriend\Model;

use Magento\Model\Exception as CoreException;

/**
 * SendFriend Log
 *
 * @method \Magento\Sendfriend\Model\Resource\Sendfriend _getResource()
 * @method \Magento\Sendfriend\Model\Resource\Sendfriend getResource()
 * @method int getIp()
 * @method \Magento\Sendfriend\Model\Sendfriend setIp(int $value)
 * @method int getTime()
 * @method \Magento\Sendfriend\Model\Sendfriend setTime(int $value)
 *
 * @category    Magento
 * @package     Magento_Sendfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sendfriend extends \Magento\Model\AbstractModel
{
    /**
     * Recipient Names
     *
     * @var array
     */
    protected $_names = array();

    /**
     * Recipient Emails
     *
     * @var array
     */
    protected $_emails = array();

    /**
     * Sender data array
     *
     * @var \Magento\Object|array
     */
    protected $_sender = array();

    /**
     * Product Instance
     *
     * @var \Magento\Catalog\Model\Product
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
     * Sendfriend data
     *
     * @var \Magento\Sendfriend\Helper\Data
     */
    protected $_sendfriendData = null;

    /**
     * Catalog image
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_catalogImage = null;

    /**
     * @var \Magento\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Catalog\Helper\Image $catalogImage
     * @param \Magento\Sendfriend\Helper\Data $sendfriendData
     * @param \Magento\Escaper $escaper
     * @param \Magento\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Magento\Sendfriend\Helper\Data $sendfriendData,
        \Magento\Escaper $escaper,
        \Magento\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_catalogImage = $catalogImage;
        $this->_sendfriendData = $sendfriendData;
        $this->_escaper = $escaper;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sendfriend\Model\Resource\Sendfriend');
    }

    /**
     * @return $this
     * @throws CoreException
     */
    public function send()
    {
        if ($this->isExceedLimit()) {
            throw new \Magento\Model\Exception(
                __('You\'ve met your limit of %1 sends in an hour.', $this->getMaxSendsToFriend())
            );
        }

        $this->inlineTranslation->suspend();

        $message = nl2br(htmlspecialchars($this->getSender()->getMessage()));
        $sender = array(
            'name' => $this->_escaper->escapeHtml($this->getSender()->getName()),
            'email' => $this->_escaper->escapeHtml($this->getSender()->getEmail())
        );

        foreach ($this->getRecipients()->getEmails() as $k => $email) {
            $name = $this->getRecipients()->getNames($k);
            $this->_transportBuilder->setTemplateIdentifier(
                $this->_sendfriendData->getEmailTemplate()
            )->setTemplateOptions(
                array(
                    'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId()
                )
            )->setFrom(
                $sender
            )->setTemplateVars(
                array(
                    'name' => $name,
                    'email' => $email,
                    'product_name' => $this->getProduct()->getName(),
                    'product_url' => $this->getProduct()->getUrlInStore(),
                    'message' => $message,
                    'sender_name' => $sender['name'],
                    'sender_email' => $sender['email'],
                    'product_image' => $this->_catalogImage->init($this->getProduct(), 'small_image')->resize(75)
                )
            )->addTo(
                $email,
                $name
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        }

        $this->inlineTranslation->resume();

        $this->_incrementSentCount();

        return $this;
    }

    /**
     * Validate Form data
     *
     * @return bool|string[]
     */
    public function validate()
    {
        $errors = array();

        $name = $this->getSender()->getName();
        if (empty($name)) {
            $errors[] = __('The sender name cannot be empty.');
        }

        $email = $this->getSender()->getEmail();
        if (empty($email) or !\Zend_Validate::is($email, 'EmailAddress')) {
            $errors[] = __('Invalid Sender Email');
        }

        $message = $this->getSender()->getMessage();
        if (empty($message)) {
            $errors[] = __('The message cannot be empty.');
        }

        if (!$this->getRecipients()->getEmails()) {
            $errors[] = __('At least one recipient must be specified.');
        }

        // validate recipients email addresses
        foreach ($this->getRecipients()->getEmails() as $email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $errors[] = __('Please enter a correct recipient email address.');
                break;
            }
        }

        $maxRecipients = $this->getMaxRecipients();
        if (count($this->getRecipients()->getEmails()) > $maxRecipients) {
            $errors[] = __('No more than %1 emails can be sent at a time.', $this->getMaxRecipients());
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }

    /**
     * Set cookie instance
     *
     * @param \Magento\Stdlib\Cookie $cookie
     * @return $this
     */
    public function setCookie($cookie)
    {
        return $this->setData('_cookie', $cookie);
    }

    /**
     * Retrieve Cookie instance
     *
     * @throws \Magento\Model\Exception
     * @return \Magento\Stdlib\Cookie
     */
    public function getCookie()
    {
        $cookie = $this->_getData('_cookie');
        if (!$cookie instanceof \Magento\Stdlib\Cookie) {
            throw new \Magento\Model\Exception(__('Please define a correct Cookie instance.'));
        }
        return $cookie;
    }

    /**
     * Set Visitor Remote Address
     *
     * @param int $ipAddr the IP address on Long Format
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setRecipients($recipients)
    {
        // validate array
        if (!is_array(
            $recipients
        ) or !isset(
            $recipients['email']
        ) or !isset(
            $recipients['name']
        ) or !is_array(
            $recipients['email']
        ) or !is_array(
            $recipients['name']
        )
        ) {
            return $this;
        }

        $emails = array();
        $names = array();
        foreach ($recipients['email'] as $k => $email) {
            if (!isset($emails[$email]) && isset($recipients['name'][$k])) {
                $emails[$email] = true;
                $names[] = $recipients['name'][$k];
            }
        }

        if ($emails) {
            $emails = array_keys($emails);
        }

        return $this->setData('_recipients', new \Magento\Object(array('emails' => $emails, 'names' => $names)));
    }

    /**
     * Retrieve Recipients object
     *
     * @return \Magento\Object
     */
    public function getRecipients()
    {
        $recipients = $this->_getData('_recipients');
        if (!$recipients instanceof \Magento\Object) {
            $recipients = new \Magento\Object(array('emails' => array(), 'names' => array()));
            $this->setData('_recipients', $recipients);
        }
        return $recipients;
    }

    /**
     * Set product instance
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        return $this->setData('_product', $product);
    }

    /**
     * Retrieve Product instance
     *
     * @throws \Magento\Model\Exception
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_getData('_product');
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            throw new \Magento\Model\Exception(__('Please define a correct Product instance.'));
        }
        return $product;
    }

    /**
     * Set Sender Information array
     *
     * @param array $sender
     * @return $this
     */
    public function setSender($sender)
    {
        if (!is_array($sender)) {
            __('Invalid Sender Information');
        }

        return $this->setData('_sender', new \Magento\Object($sender));
    }

    /**
     * Retrieve Sender Information Object
     *
     * @throws \Magento\Model\Exception
     * @return \Magento\Object
     */
    public function getSender()
    {
        $sender = $this->_getData('_sender');
        if (!$sender instanceof \Magento\Object) {
            throw new \Magento\Model\Exception(__('Please define the correct Sender information.'));
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
        return $this->_sendfriendData->getMaxEmailPerPeriod();
    }

    /**
     * Get max allowed recipients for "Send to a Friend" function
     *
     * @return integer
     */
    public function getMaxRecipients()
    {
        return $this->_sendfriendData->getMaxRecipients();
    }

    /**
     * Check if user is allowed to email product to a friend
     *
     * @return boolean
     */
    public function canEmailToFriend()
    {
        return $this->_sendfriendData->isEnabled();
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

        switch ($this->_sendfriendData->getLimitBy()) {
            case \Magento\Sendfriend\Helper\Data::CHECK_COOKIE:
                return $this->_sentCount = $this->_sentCountByCookies(false);
            case \Magento\Sendfriend\Helper\Data::CHECK_IP:
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
        switch ($this->_sendfriendData->getLimitBy()) {
            case \Magento\Sendfriend\Helper\Data::CHECK_COOKIE:
                return $this->_sentCount = $this->_sentCountByCookies(true);
            case \Magento\Sendfriend\Helper\Data::CHECK_IP:
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
        $cookie = $this->_sendfriendData->getCookieName();
        $time = time();
        $newTimes = array();

        if (isset($this->_lastCookieValue[$cookie])) {
            $oldTimes = $this->_lastCookieValue[$cookie];
        } else {
            $oldTimes = $this->getCookie()->get($cookie);
        }

        if ($oldTimes) {
            $oldTimes = explode(',', $oldTimes);
            foreach ($oldTimes as $oldTime) {
                $periodTime = $time - $this->_sendfriendData->getPeriod();
                if (is_numeric($oldTime) and $oldTime >= $periodTime) {
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
        $time = time();
        $period = $this->_sendfriendData->getPeriod();
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
     * @return $this
     */
    public function register()
    {
        if (!$this->_registry->registry('send_to_friend_model')) {
            $this->_registry->register('send_to_friend_model', $this);
        }
        return $this;
    }
}
