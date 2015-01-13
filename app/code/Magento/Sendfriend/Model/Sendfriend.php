<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sendfriend\Model;

use Magento\Framework\Model\Exception as CoreException;

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
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sendfriend extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Recipient Names
     *
     * @var array
     */
    protected $_names = [];

    /**
     * Recipient Emails
     *
     * @var array
     */
    protected $_emails = [];

    /**
     * Sender data array
     *
     * @var \Magento\Framework\Object|array
     */
    protected $_sender = [];

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
    protected $_lastCookieValue = [];

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
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Catalog\Helper\Image $catalogImage
     * @param \Magento\Sendfriend\Helper\Data $sendfriendData
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Magento\Sendfriend\Helper\Data $sendfriendData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_catalogImage = $catalogImage;
        $this->_sendfriendData = $sendfriendData;
        $this->_escaper = $escaper;
        $this->remoteAddress = $remoteAddress;
        $this->cookieManager = $cookieManager;
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
            throw new \Magento\Framework\Model\Exception(
                __('You\'ve met your limit of %1 sends in an hour.', $this->getMaxSendsToFriend())
            );
        }

        $this->inlineTranslation->suspend();

        $message = nl2br(htmlspecialchars($this->getSender()->getMessage()));
        $sender = [
            'name' => $this->_escaper->escapeHtml($this->getSender()->getName()),
            'email' => $this->_escaper->escapeHtml($this->getSender()->getEmail()),
        ];

        foreach ($this->getRecipients()->getEmails() as $k => $email) {
            $name = $this->getRecipients()->getNames($k);
            $this->_transportBuilder->setTemplateIdentifier(
                $this->_sendfriendData->getEmailTemplate()
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )->setFrom(
                $sender
            )->setTemplateVars(
                [
                    'name' => $name,
                    'email' => $email,
                    'product_name' => $this->getProduct()->getName(),
                    'product_url' => $this->getProduct()->getUrlInStore(),
                    'message' => $message,
                    'sender_name' => $sender['name'],
                    'sender_email' => $sender['email'],
                    'product_image' => $this->_catalogImage->init($this->getProduct(), 'small_image')->resize(75),
                ]
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
        $errors = [];

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

        $emails = [];
        $names = [];
        foreach ($recipients['email'] as $k => $email) {
            if (!isset($emails[$email]) && isset($recipients['name'][$k])) {
                $emails[$email] = true;
                $names[] = $recipients['name'][$k];
            }
        }

        if ($emails) {
            $emails = array_keys($emails);
        }

        return $this->setData(
            '_recipients',
            new \Magento\Framework\Object(['emails' => $emails, 'names' => $names])
        );
    }

    /**
     * Retrieve Recipients object
     *
     * @return \Magento\Framework\Object
     */
    public function getRecipients()
    {
        $recipients = $this->_getData('_recipients');
        if (!$recipients instanceof \Magento\Framework\Object) {
            $recipients = new \Magento\Framework\Object(['emails' => [], 'names' => []]);
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
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_getData('_product');
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            throw new \Magento\Framework\Model\Exception(__('Please define a correct Product instance.'));
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

        return $this->setData('_sender', new \Magento\Framework\Object($sender));
    }

    /**
     * Retrieve Sender Information Object
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\Object
     */
    public function getSender()
    {
        $sender = $this->_getData('_sender');
        if (!$sender instanceof \Magento\Framework\Object) {
            throw new \Magento\Framework\Model\Exception(__('Please define the correct Sender information.'));
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
        $cookieName = $this->_sendfriendData->getCookieName();
        $time = time();
        $newTimes = [];

        if (isset($this->_lastCookieValue[$cookieName])) {
            $oldTimes = $this->_lastCookieValue[$cookieName];
        } else {
            $oldTimes = $this->cookieManager->getCookie($cookieName);
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
            $this->cookieManager->setSensitiveCookie($cookieName, $newValue);
            $this->_lastCookieValue[$cookieName] = $newValue;
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
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        if ($increment) {
            // delete expired logs
            $this->_getResource()->deleteLogsBefore($time - $period);
            // add new item
            $this->_getResource()->addSendItem($this->remoteAddress->getRemoteAddress(true), $time, $websiteId);
        }

        return $this->_getResource()->getSendCount(
            $this,
            $this->remoteAddress->getRemoteAddress(true),
            time() - $period,
            $websiteId
        );
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
