<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Newsletter\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Subscriber model
 *
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
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Subscriber extends AbstractModel
{
    public const STATUS_SUBSCRIBED = 1;
    public const STATUS_NOT_ACTIVE = 2;
    public const STATUS_UNSUBSCRIBED = 3;
    public const STATUS_UNCONFIRMED = 4;

    public const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'newsletter/subscription/confirm_email_template';
    public const XML_PATH_CONFIRM_EMAIL_IDENTITY = 'newsletter/subscription/confirm_email_identity';
    public const XML_PATH_SUCCESS_EMAIL_TEMPLATE = 'newsletter/subscription/success_email_template';
    public const XML_PATH_SUCCESS_EMAIL_IDENTITY = 'newsletter/subscription/success_email_identity';
    public const XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE = 'newsletter/subscription/un_email_template';
    public const XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY = 'newsletter/subscription/un_email_identity';
    public const XML_PATH_CONFIRMATION_FLAG = 'newsletter/subscription/confirm';
    public const XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG = 'newsletter/subscription/allow_guest_subscribe';

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
     *
     * @var Data
     */
    protected $_newsletterData = null;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Date
     * @var DateTime
     */
    private $dateTime;

    /**
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $newsletterData
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param StateInterface $inlineTranslation
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DateTime|null $dateTime
     * @param CustomerInterfaceFactory|null $customerFactory
     * @param DataObjectHelper|null $dataObjectHelper
     * @param SubscriptionManagerInterface|null $subscriptionManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $newsletterData,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        StateInterface $inlineTranslation,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?DateTime $dateTime = null,
        ?CustomerInterfaceFactory $customerFactory = null,
        ?DataObjectHelper $dataObjectHelper = null,
        ?SubscriptionManagerInterface $subscriptionManager = null
    ) {
        $this->_newsletterData = $newsletterData;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()->get(
            DateTime::class
        );
        $this->customerFactory = $customerFactory ?: ObjectManager::getInstance()
            ->get(CustomerInterfaceFactory::class);
        $this->dataObjectHelper = $dataObjectHelper ?: ObjectManager::getInstance()
            ->get(DataObjectHelper::class);
        $this->customerRepository = $customerRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->inlineTranslation = $inlineTranslation;
        $this->subscriptionManager = $subscriptionManager ?: ObjectManager::getInstance()
            ->get(SubscriptionManagerInterface::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Subscriber::class);
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
        if ($this->getSubscriberStatus() !== $value) {
            $this->setStatusChanged(true);
        }

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
    public function setStatusChanged($value)
    {
        $this->_isStatusChanged = (boolean) $value;
        return $this;
    }

    /**
     * Return status change flag value
     *
     * @return boolean
     */
    public function isStatusChanged()
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
        return $this->getId() && (int)$this->getStatus() === self::STATUS_SUBSCRIBED;
    }

    /**
     * Load by subscriber email
     *
     * @param string $email
     * @param int $websiteId
     * @return $this
     * @since 100.4.0
     */
    public function loadBySubscriberEmail(string $email, int $websiteId): Subscriber
    {
        /** @var ResourceModel\Subscriber $resource */
        $resource = $this->getResource();
        $data = $resource->loadBySubscriberEmail($email, $websiteId);
        $this->addData($data);
        $this->setOrigData();

        return $this;
    }

    /**
     * Load by customer id
     *
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     * @since 100.4.0
     */
    public function loadByCustomer(int $customerId, int $websiteId): Subscriber
    {
        /** @var ResourceModel\Subscriber $resource */
        $resource = $this->getResource();
        $data = $resource->loadByCustomerId($customerId, $websiteId);
        $this->addData($data);
        $this->setOrigData();

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
        $par = [];
        $char = array_merge(range('a', 'z'), range(0, 9));
        $charLen = count($char) - 1;
        for ($i = 0; $i < $length; $i++) {
            $disc = Random::getRandomNumber(0, $charLen);
            $par[$i] = $char[$disc];
            $id = $id . $char[$disc];
        }
        return $id;
    }

    /**
     * Unsubscribes loaded subscription
     *
     * @throws LocalizedException
     * @return $this
     */
    public function unsubscribe()
    {
        if ($this->hasCheckCode() && $this->getCode() != $this->getCheckCode()) {
            throw new LocalizedException(
                __('This is an invalid subscription confirmation code.')
            );
        }

        if ($this->getSubscriberStatus() != self::STATUS_UNSUBSCRIBED) {
            $this->setStatus(self::STATUS_UNSUBSCRIBED);
            $this->save();
            $this->sendUnsubscriptionEmail();
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
            $this->setStatus(self::STATUS_SUBSCRIBED)
                ->setStatusChanged(true)
                ->save();

            $this->sendConfirmationSuccessEmail();
            return true;
        }

        return false;
    }

    /**
     * Mark receiving subscriber of queue newsletter
     *
     * @param Queue $queue
     * @return Subscriber
     */
    public function received(Queue $queue)
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
        $vars = [
            'store' => $this->_storeManager->getStore($this->getStoreId()),
            'subscriber_data' => [
                'confirmation_link' => $this->getConfirmationLink(),
            ],
        ];
        $this->sendEmail(self::XML_PATH_CONFIRM_EMAIL_TEMPLATE, self::XML_PATH_CONFIRM_EMAIL_IDENTITY, $vars);

        return $this;
    }

    /**
     * Sends out confirmation success email
     *
     * @return $this
     */
    public function sendConfirmationSuccessEmail()
    {
        $this->sendEmail(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE, self::XML_PATH_SUCCESS_EMAIL_IDENTITY);

        return $this;
    }

    /**
     * Sends out unsubscription email
     *
     * @return $this
     */
    public function sendUnsubscriptionEmail()
    {
        $this->sendEmail(self::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE, self::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY);

        return $this;
    }

    /**
     * Send email about change status
     *
     * @param string $emailTemplatePath
     * @param string $emailIdentityPath
     * @param array $templateVars
     * @return void
     */
    private function sendEmail(string $emailTemplatePath, string $emailIdentityPath, array $templateVars = []): void
    {
        if ($this->getImportMode()) {
            return;
        }

        $template = $this->_scopeConfig->getValue($emailTemplatePath, ScopeInterface::SCOPE_STORE, $this->getStoreId());
        $identity = $this->_scopeConfig->getValue($emailIdentityPath, ScopeInterface::SCOPE_STORE, $this->getStoreId());
        if (!$template || !$identity) {
            return;
        }

        $templateVars += ['subscriber' => $this];
        $this->inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $this->getStoreId(),
            ]
        )->setTemplateVars(
            $templateVars
        )->setFromByScope(
            $identity,
            $this->getStoreId()
        )->addTo(
            $this->getEmail(),
            $this->getName()
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }

    /**
     * Retrieve Subscribers Full Name if it was set
     *
     * @return string|null
     */
    public function getSubscriberFullName()
    {
        $name = null;
        if ($this->hasFirstname() || $this->hasLastname()) {
            $name = $this->getFirstname() . ' ' . $this->getLastname();
        }
        return $name;
    }

    /**
     * Set date of last changed status
     *
     * @return $this
     * @since 100.2.1
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->dataHasChangedFor('subscriber_status')) {
            $this->setChangeStatusAt($this->dateTime->gmtDate());
        }
        return $this;
    }

    /**
     * Load subscriber data from resource model by email
     *
     * @param string $subscriberEmail
     * @return $this
     * @deprecated 100.4.0 The subscription should be loaded by website id
     * @see loadBySubscriberEmail
     */
    public function loadByEmail($subscriberEmail)
    {
        $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
        $this->loadBySubscriberEmail($subscriberEmail, $websiteId);

        return $this;
    }

    /**
     * Load subscriber info by customerId
     *
     * @param int $customerId
     * @return $this
     * @deprecated 100.4.0 The subscription should be loaded by website id
     * @see loadByCustomer
     */
    public function loadByCustomerId($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
            $this->loadByCustomer((int)$customerId, $websiteId);
            if ($this->getId() && $customer->getId() && !$this->getCustomerId()) {
                $this->setCustomerId($customer->getId());
                $this->setSubscriberConfirmCode($this->randomSequence());
                $this->save();
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (NoSuchEntityException $e) {
        }
        return $this;
    }

    /**
     * Subscribes by email
     *
     * @param string $email
     * @return int
     * @deprecated 100.4.0 The subscription should be updated by store id
     * @see \Magento\Newsletter\Model\SubscriptionManager::subscribe
     */
    public function subscribe($email)
    {
        $storeId = (int)$this->_storeManager->getStore()->getId();
        $subscriber = $this->subscriptionManager->subscribe($email, $storeId);
        $this->addData($subscriber->getData());

        return $this->getStatus();
    }

    /**
     * Subscribe the customer with the id provided
     *
     * @param int $customerId
     * @return $this
     * @deprecated 100.4.0 The subscription should be updated by store id
     * @see \Magento\Newsletter\Model\SubscriptionManager::subscribeCustomer
     */
    public function subscribeCustomerById($customerId)
    {
        return $this->_updateCustomerSubscription($customerId, true);
    }

    /**
     * Unsubscribe the customer with the id provided
     *
     * @param int $customerId
     * @return $this
     * @deprecated 100.4.0 The subscription should be updated by store id
     * @see \Magento\Newsletter\Model\SubscriptionManager::unsubscribeCustomer
     */
    public function unsubscribeCustomerById($customerId)
    {
        return $this->_updateCustomerSubscription($customerId, false);
    }

    /**
     * Update the subscription based on latest information of associated customer.
     *
     * @param int $customerId
     * @return $this
     * @deprecated 100.4.0 The subscription should be updated by store id
     * @see \Magento\Newsletter\Model\SubscriptionManager::subscribeCustomer
     */
    public function updateSubscription($customerId)
    {
        $this->loadByCustomerId($customerId);
        $this->_updateCustomerSubscription($customerId, $this->isSubscribed());
        return $this;
    }

    /**
     * Saving customer subscription status
     *
     * @param int $customerId
     * @param bool $subscribe indicates whether the customer should be subscribed or unsubscribed
     * @return $this
     * @deprecated 100.4.0 The subscription should be updated by store id
     * @see \Magento\Newsletter\Model\SubscriptionManager::subscribeCustomer
     */
    protected function _updateCustomerSubscription($customerId, $subscribe)
    {
        $storeId = (int)$this->_storeManager->getStore()->getId();
        if ($subscribe) {
            $subscriber = $this->subscriptionManager->subscribeCustomer((int)$customerId, $storeId);
        } else {
            $subscriber = $this->subscriptionManager->unsubscribeCustomer((int)$customerId, $storeId);
        }
        $this->addData($subscriber->getData());

        return $this;
    }
}
