<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Controller\Subscriber as SubscriberController;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\Config as NewsletterConfig;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * New newsletter subscription action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewAction extends SubscriberController implements HttpPostActionInterface
{
    /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var NewsletterConfig
     */
    private $newsletterConfig;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param EmailValidator|null $emailValidator
     * @param CustomerRepositoryInterface|null $customerRepository
     * @param NewsletterConfig|null $newsletterConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        SubscriptionManagerInterface $subscriptionManager,
        ?EmailValidator $emailValidator = null,
        ?CustomerRepositoryInterface $customerRepository = null,
        ?NewsletterConfig $newsletterConfig = null
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->subscriptionManager = $subscriptionManager;
        $this->emailValidator = $emailValidator ?: ObjectManager::getInstance()->get(EmailValidator::class);
        $this->customerRepository = $customerRepository ?: ObjectManager::getInstance()
            ->get(CustomerRepositoryInterface::class);
        $this->newsletterConfig = $newsletterConfig?: ObjectManager::getInstance()
            ->get(NewsletterConfig::class);
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl
        );
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     *
     * @return void
     * @throws LocalizedException
     */
    protected function validateEmailAvailable($email)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($this->_customerSession->isLoggedIn()
            && ($this->_customerSession->getCustomerDataObject()->getEmail() !== $email
            && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId))
        ) {
            throw new LocalizedException(
                __('This email address is already assigned to another user.')
            );
        }
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @throws LocalizedException
     * @return void
     */
    protected function validateGuestSubscription()
    {
        if ($this->_objectManager->get(ScopeConfigInterface::class)
                ->getValue(
                    Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                    ScopeInterface::SCOPE_STORE
                ) != 1
            && !$this->_customerSession->isLoggedIn()
        ) {
            throw new LocalizedException(
                __(
                    'Sorry, but the administrator denied subscription for guests. Please <a href="%1">register</a>.',
                    $this->_customerUrl->getRegisterUrl()
                )
            );
        }
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     *
     * @return void
     * @throws LocalizedException
     */
    protected function validateEmailFormat($email)
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }
    }

    /**
     * New subscription action
     *
     * @return Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()
            && $this->getRequest()->getPost('email')
            && $this->newsletterConfig->isActive()
        ) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
                /** @var Subscriber $subscriber */
                $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
                if ($subscriber->getId()
                    && (int)$subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
                    throw new LocalizedException(
                        __('This email address is already subscribed.')
                    );
                }

                $storeId = (int)$this->_storeManager->getStore()->getId();
                $currentCustomerId = $this->getCustomerId($email, $websiteId);

                $subscriber = $currentCustomerId
                    ? $this->subscriptionManager->subscribeCustomer($currentCustomerId, $storeId)
                    : $this->subscriptionManager->subscribe($email, $storeId);
                $message = $this->getSuccessMessage((int)$subscriber->getSubscriberStatus());
                $this->messageManager->addSuccessMessage($message);
            } catch (LocalizedException $e) {
                $this->messageManager->addComplexErrorMessage(
                    'localizedSubscriptionErrorMessage',
                    ['message' => $e->getMessage()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong with the subscription.'));
            }
        }
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $redirectUrl = $this->_redirect->getRedirectUrl();

        return $redirect->setUrl($redirectUrl);
    }

    /**
     * Check if customer with provided email exists and return its id
     *
     * @param string $email
     * @param int $websiteId
     *
     * @return int|null
     */
    private function getCustomerId(string $email, int $websiteId): ?int
    {
        try {
            $customer = $this->customerRepository->get($email, $websiteId);
            return (int)$customer->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get success message
     *
     * @param int $status
     *
     * @return Phrase
     */
    private function getSuccessMessage(int $status): Phrase
    {
        if ($status === Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }

        return __('Thank you for your subscription.');
    }
}
