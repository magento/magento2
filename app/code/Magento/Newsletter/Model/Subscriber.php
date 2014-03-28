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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Newsletter\Model;

/**
 * Subscriber model
 *
 * @method \Magento\Newsletter\Model\Resource\Subscriber _getResource()
 * @method \Magento\Newsletter\Model\Resource\Subscriber getResource()
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getChangeStatusAt()
 * @method $this setChangeStatusAt(string $value)
 * @method int getCustomerId()
 * @method $this setCustomerId(int $value)
 * @method string getSubscriberEmail()
 * @method $this setSubscriberEmail(string $value)
 * @method int getSubscriberStatus()
 * @method $this setSubscriberStatus(int $value)
 * @method string getSubscriberConfirmCode()
 * @method $this setSubscriberConfirmCode(string $value)
 * @method int getSubscriberId()
 * @method Subscriber setSubscriberId(int $value)
 */
class Subscriber extends \Magento\Model\AbstractModel
{
    const STATUS_SUBSCRIBED = 1;

    const STATUS_NOT_ACTIVE = 2;

    const STATUS_UNSUBSCRIBED = 3;

    const STATUS_UNCONFIRMED = 4;

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'newsletter/subscription/confirm_email_template';

    const XML_PATH_CONFIRM_EMAIL_IDENTITY = 'newsletter/subscription/confirm_email_identity';

    const XML_PATH_SUCCESS_EMAIL_TEMPLATE = 'newsletter/subscription/success_email_template';

    const XML_PATH_SUCCESS_EMAIL_IDENTITY = 'newsletter/subscription/success_email_identity';

    const XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE = 'newsletter/subscription/un_email_template';

    const XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY = 'newsletter/subscription/un_email_identity';

    const XML_PATH_CONFIRMATION_FLAG = 'newsletter/subscription/confirm';

    const XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG = 'newsletter/subscription/allow_guest_subscribe';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'newsletter_subscriber';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'subscriber';

    /**
     * True if data changed
     *
     * @var bool
     */
    protected $_isStatusChanged = false;

    /**
     * Newsletter data
     *
     * @var \Magento\Newsletter\Helper\Data
     */
    protected $_newsletterData = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Customer factory
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Newsletter\Helper\Data $newsletterData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Newsletter\Helper\Data $newsletterData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_newsletterData = $newsletterData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
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
        $this->_init('Magento\Newsletter\Model\Resource\Subscriber');
    }

    /**
     * Alias for getSubscriberId()
     *
     * @return int
     */
    public function getId()
    {
        return $this->getSubscriberId();
    }

    /**
     * Alias for setSubscriberId()
     *
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->setSubscriberId($value);
    }

    /**
     * Alias for getSubscriberConfirmCode()
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getSubscriberConfirmCode();
    }

    /**
     * Return link for confirmation of subscription
     *
     * @return string
     */
    public function getConfirmationLink()
    {
        return $this->_newsletterData->getConfirmationUrl($this);
    }

    /**
     * Returns Unsubscribe url
     *
     * @return string
     */
    public function getUnsubscriptionLink()
    {
        return $this->_newsletterData->getUnsubscribeUrl($this);
    }

    /**
     * Alias for setSubscriberConfirmCode()
     *
     * @param string $value
     * @return $this
     */
    public function setCode($value)
    {
        return $this->setSubscriberConfirmCode($value);
    }

    /**
     * Alias for getSubscriberStatus()
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getSubscriberStatus();
    }

    /**
     * Alias for setSubscriberStatus()
     *
     * @param int $value
     * @return $this
     */
    public function setStatus($value)
    {
        return $this->setSubscriberStatus($value);
    }

    /**
     * Set the error messages scope for subscription
     *
     * @param boolean $scope
     * @return $this
     */
    public function setMessagesScope($scope)
    {
        $this->getResource()->setMessagesScope($scope);
        return $this;
    }

    /**
     * Alias for getSubscriberEmail()
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getSubscriberEmail();
    }

    /**
     * Alias for setSubscriberEmail()
     *
     * @param string $value
     * @return $this
     */
    public function setEmail($value)
    {
        return $this->setSubscriberEmail($value);
    }

    /**
     * Set for status change flag
     *
     * @param boolean $value
     * @return $this
     */
    public function setIsStatusChanged($value)
    {
        $this->_isStatusChanged = (bool)$value;
        return $this;
    }

    /**
     * Return status change flag value
     *
     * @return boolean
     */
    public function getIsStatusChanged()
    {
        return $this->_isStatusChanged;
    }

    /**
     * Return customer subscription status
     *
     * @return bool
     */
    public function isSubscribed()
    {
        if ($this->getId() && $this->getStatus() == self::STATUS_SUBSCRIBED) {
            return true;
        }

        return false;
    }

    /**
     * Load subscriber data from resource model by email
     *
     * @param string $subscriberEmail
     * @return $this
     */
    public function loadByEmail($subscriberEmail)
    {
        $this->addData($this->getResource()->loadByEmail($subscriberEmail));
        return $this;
    }

    /**
     * Load subscriber info by customerId
     *
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomer($customerId)
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->_customerFactory->create()->load($customerId);
        $data = $this->getResource()->loadByCustomer($customer);
        $this->addData($data);
        if (!empty($data) && $customer->getId() && !$this->getCustomerId()) {
            $this->setCustomerId($customer->getId());
            $this->setSubscriberConfirmCode($this->randomSequence());
            if ($this->getStatus() == self::STATUS_NOT_ACTIVE) {
                $this->setStatus($customer->getIsSubscribed() ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED);
            }
            $this->save();
        }
        return $this;
    }

    /**
     * Returns string of random chars
     *
     * @param int $length
     * @return string
     */
    public function randomSequence($length = 32)
    {
        $id = '';
        $par = array();
        $char = array_merge(range('a', 'z'), range(0, 9));
        $charLen = count($char) - 1;
        for ($i = 0; $i < $length; $i++) {
            $disc = mt_rand(0, $charLen);
            $par[$i] = $char[$disc];
            $id = $id . $char[$disc];
        }
        return $id;
    }

    /**
     * Subscribes by email
     *
     * @param string $email
     * @throws \Exception
     * @return int
     */
    public function subscribe($email)
    {
        $this->loadByEmail($email);

        if (!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed = $this->_coreStoreConfig->getConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1 ? true : false;
        $isOwnSubscribes = false;
        $ownerId = $this->_customerFactory->create()->setWebsiteId(
            $this->_storeManager->getStore()->getWebsiteId()
        )->loadByEmail(
            $email
        )->getId();
        $isSubscribeOwnEmail = $this->_customerSession->isLoggedIn() && $ownerId == $this->_customerSession->getId();

        if (!$this->getId() ||
            $this->getStatus() == self::STATUS_UNSUBSCRIBED ||
            $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true) {
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
        }

        if ($isSubscribeOwnEmail) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->_customerFactory->create()->load($this->_customerSession->getCustomerId());
            $this->setStoreId($customer->getStoreId());
            $this->setCustomerId($customer->getId());
        } else {
            $this->setStoreId($this->_storeManager->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if ($isConfirmNeed === true && $isOwnSubscribes === false) {
                $this->sendConfirmationRequestEmail();
            } else {
                $this->sendConfirmationSuccessEmail();
            }

            return $this->getStatus();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Unsubscribes loaded subscription
     *
     * @throws \Magento\Model\Exception
     * @return $this
     */
    public function unsubscribe()
    {
        if ($this->hasCheckCode() && $this->getCode() != $this->getCheckCode()) {
            throw new \Magento\Model\Exception(__('This is an invalid subscription confirmation code.'));
        }

        $this->setSubscriberStatus(self::STATUS_UNSUBSCRIBED)->save();
        $this->sendUnsubscriptionEmail();
        return $this;
    }

    /**
     * Update newsletter subscription for given customer
     *
     * @param int $customerId
     * @param boolean $subscribe
     *
     * @return  \Magento\Newsletter\Model\Subscriber
     */
    public function updateSubscription($customerId, $subscribe)
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_customerFactory->create()->load($customerId);
        $customerModel->setIsSubscribed($subscribe);
        $this->subscribeCustomer($customerModel);

        return $this;
    }

    /**
     * Saving customer subscription status
     *
     * @param   \Magento\Customer\Model\Customer $customer
     * @return  $this
     */
    public function subscribeCustomer($customer)
    {
        $this->loadByCustomer($customer->getId());

        if ($customer->getImportMode()) {
            $this->setImportMode(true);
        }

        if (!$customer->getIsSubscribed() && !$this->getId()) {
            // If subscription flag not set or customer is not a subscriber
            // and no subscribe below
            return $this;
        }

        if (!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        /*
         * Logical mismatch between customer registration confirmation code and customer password confirmation
         */
        $confirmation = null;
        if ($customer->isConfirmationRequired() && $customer->getConfirmation() != $customer->getPassword()) {
            $confirmation = $customer->getConfirmation();
        }

        $sendInformationEmail = false;
        if ($customer->hasIsSubscribed()) {
            $status = $customer->getIsSubscribed() ? !is_null(
                $confirmation
            ) ? self::STATUS_UNCONFIRMED : self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED;
            /**
             * If subscription status has been changed then send email to the customer
             */
            if ($status != self::STATUS_UNCONFIRMED && $status != $this->getStatus()) {
                $sendInformationEmail = true;
            }
        } elseif ($this->getStatus() == self::STATUS_UNCONFIRMED && is_null($confirmation)) {
            $status = self::STATUS_SUBSCRIBED;
            $sendInformationEmail = true;
        } else {
            $status = $this->getStatus() == self::STATUS_NOT_ACTIVE ? self::STATUS_UNSUBSCRIBED : $this->getStatus();
        }

        if ($status != $this->getStatus()) {
            $this->setIsStatusChanged(true);
        }

        $this->setStatus($status);

        if (!$this->getId()) {
            $storeId = $customer->getStoreId();
            if ($customer->getStoreId() == 0) {
                $storeId = $this->_storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            }
            $this->setStoreId($storeId)->setCustomerId($customer->getId())->setEmail($customer->getEmail());
        } else {
            $this->setStoreId($customer->getStoreId())->setEmail($customer->getEmail());
        }

        $this->save();
        $sendSubscription = $customer->getData('sendSubscription') || $sendInformationEmail;
        if (is_null($sendSubscription) xor $sendSubscription) {
            if ($this->getIsStatusChanged() && $status == self::STATUS_UNSUBSCRIBED) {
                $this->sendUnsubscriptionEmail();
            } elseif ($this->getIsStatusChanged() && $status == self::STATUS_SUBSCRIBED) {
                $this->sendConfirmationSuccessEmail();
            }
        }
        return $this;
    }

    /**
     * Confirms subscriber newsletter
     *
     * @param string $code
     * @return boolean
     */
    public function confirm($code)
    {
        if ($this->getCode() == $code) {
            $this->setStatus(self::STATUS_SUBSCRIBED)->setIsStatusChanged(true)->save();
            return true;
        }

        return false;
    }

    /**
     * Mark receiving subscriber of queue newsletter
     *
     * @param  \Magento\Newsletter\Model\Queue $queue
     * @return boolean
     */
    public function received(\Magento\Newsletter\Model\Queue $queue)
    {
        $this->getResource()->received($this, $queue);
        return $this;
    }

    /**
     * Sends out confirmation email
     *
     * @return $this
     */
    public function sendConfirmationRequestEmail()
    {
        if ($this->getImportMode()) {
            return $this;
        }

        if (!$this->_coreStoreConfig->getConfig(
            self::XML_PATH_CONFIRM_EMAIL_TEMPLATE
        ) || !$this->_coreStoreConfig->getConfig(
            self::XML_PATH_CONFIRM_EMAIL_IDENTITY
        )
        ) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $this->_transportBuilder->setTemplateIdentifier(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE)
        )->setTemplateOptions(
            array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            )
        )->setTemplateVars(
            array('subscriber' => $this, 'store' => $this->_storeManager->getStore())
        )->setFrom(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_CONFIRM_EMAIL_IDENTITY)
        )->addTo(
            $this->getEmail(),
            $this->getName()
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Sends out confirmation success email
     *
     * @return $this
     */
    public function sendConfirmationSuccessEmail()
    {
        if ($this->getImportMode()) {
            return $this;
        }

        if (!$this->_coreStoreConfig->getConfig(
            self::XML_PATH_SUCCESS_EMAIL_TEMPLATE
        ) || !$this->_coreStoreConfig->getConfig(
            self::XML_PATH_SUCCESS_EMAIL_IDENTITY
        )
        ) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $this->_transportBuilder->setTemplateIdentifier(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE)
        )->setTemplateOptions(
            array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            )
        )->setTemplateVars(
            array('subscriber' => $this)
        )->setFrom(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY)
        )->addTo(
            $this->getEmail(),
            $this->getName()
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Sends out unsubsciption email
     *
     * @return $this
     */
    public function sendUnsubscriptionEmail()
    {
        if ($this->getImportMode()) {
            return $this;
        }
        if (!$this->_coreStoreConfig->getConfig(
            self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE
        ) || !$this->_coreStoreConfig->getConfig(
            self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY
        )
        ) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $this->_transportBuilder->setTemplateIdentifier(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE)
        )->setTemplateOptions(
            array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            )
        )->setTemplateVars(
            array('subscriber' => $this)
        )->setFrom(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY)
        )->addTo(
            $this->getEmail(),
            $this->getName()
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Retrieve Subscribers Full Name if it was set
     *
     * @return string|null
     */
    public function getSubscriberFullName()
    {
        $name = null;
        if ($this->hasCustomerFirstname() || $this->hasCustomerLastname()) {
            $name = $this->getCustomerFirstname() . ' ' . $this->getCustomerLastname();
        }
        return $name;
    }
}
