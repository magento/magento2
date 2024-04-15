<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Adminhtml\Form\Element\Newsletter\Subscriptions as SubscriptionsElement;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Newsletter extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Customer::tab/newsletter.phtml';

    /**
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param AccountManagementInterface $customerAccountManagement
     * @param SystemStore $systemStore
     * @param CustomerRepositoryInterface $customerRepository
     * @param Share $shareConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        AccountManagementInterface $customerAccountManagement,
        SystemStore $systemStore,
        CustomerRepositoryInterface $customerRepository,
        Share $shareConfig,
        array $data = []
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->systemStore = $systemStore;
        $this->customerRepository = $customerRepository;
        $this->shareConfig = $shareConfig;
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Newsletter');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Newsletter');
    }

    /**
     * @inheritdoc
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return (bool)$this->getCurrentCustomerId();
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $this->initForm();

        return $this;
    }

    /**
     * Init form values
     *
     * @return $this
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_newsletter');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Newsletter Information'),
                'class' => 'customer-newsletter-fieldset' . (!$this->isSingleWebsiteMode() ? ' multi-website' : ''),
            ]
        );

        $customerSubscriptions = $this->getCustomerSubscriptionsOnWebsites();
        if (empty($customerSubscriptions)) {
            return $this;
        }

        if ($this->isSingleWebsiteMode()) {
            $this->prepareFormSingleWebsite($fieldset, $customerSubscriptions);
            $this->updateFromSession($form, $this->getCurrentCustomerId());
        } else {
            $this->prepareFormMultiplyWebsite($fieldset, $customerSubscriptions);
        }

        if ($this->customerAccountManagement->isReadonly($this->getCurrentCustomerId())) {
            $fieldset->setReadonly(true, true);
        }

        return $this;
    }

    /**
     * Prepare form fields for single website mode
     *
     * @param Fieldset $fieldset
     * @param array $subscriptions
     * @return void
     */
    private function prepareFormSingleWebsite(Fieldset $fieldset, array $subscriptions): void
    {
        $customer = $this->getCurrentCustomer();
        $websiteId = (int)$this->_storeManager->getStore($customer->getStoreId())->getWebsiteId();
        $customerSubscription = $subscriptions[$websiteId] ?? $this->retrieveSubscriberData($customer, $websiteId);

        $checkboxElement = $fieldset->addField(
            'subscription_status_' . $websiteId,
            'checkbox',
            [
                'label' => __('Subscribed to Newsletter'),
                'name' => "subscription_status[$websiteId]",
                'data-form-part' => $this->getData('target_form'),
                'value' => $customerSubscription['status'],
                'onchange' => 'this.value = this.checked;',
            ]
        );
        $checkboxElement->setIsChecked($customerSubscription['status']);
        if (!$this->isSingleStoreMode()) {
            $fieldset->addField(
                'subscription_store_' . $websiteId,
                'select',
                [
                    'label' => __('Subscribed on Store View'),
                    'name' => "subscription_store[$websiteId]",
                    'data-form-part' => $this->getData('target_form'),
                    'values' => $customerSubscription['store_options'],
                    'value' => $customerSubscription['store_id'] ?? null,
                ]
            );
        }
        if (!empty($customerSubscription['last_updated'])) {
            $text = $customerSubscription['status'] ? __('Last Date Subscribed') : __('Last Date Unsubscribed');
            $fieldset->addField(
                'change_status_date_' . $websiteId,
                'label',
                [
                    'label' => $text,
                    'value' => $customerSubscription['last_updated'],
                    'bold' => true
                ]
            );
        }
    }

    /**
     * Prepare form fields for multiply website mode
     *
     * @param Fieldset $fieldset
     * @param array $subscriptions
     * @return void
     */
    private function prepareFormMultiplyWebsite(Fieldset $fieldset, array $subscriptions): void
    {
        $fieldset->addType('customer_subscription', SubscriptionsElement::class);
        $fieldset->addField(
            'subscription',
            'customer_subscription',
            [
                'label' => __('Subscribed to Newsletter'),
                'name' => 'subscription',
                'subscriptions' => $subscriptions,
                'target_form' => $this->getData('target_form'),
                'class' => 'newsletter-subscriptions',
                'customer_id' => $this->getCurrentCustomerId(),
            ]
        );
    }

    /**
     * Get current customer id
     *
     * @return int
     */
    private function getCurrentCustomerId(): int
    {
        return (int)$this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get current customer model
     *
     * @return CustomerInterface|null
     */
    private function getCurrentCustomer(): ?CustomerInterface
    {
        $customerId = $this->getCurrentCustomerId();
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $customer;
    }

    /**
     * Get Customer Subscriptions on Websites
     *
     * @return array
     */
    private function getCustomerSubscriptionsOnWebsites(): array
    {
        $customer = $this->getCurrentCustomer();
        if ($customer === null) {
            return [];
        }

        $subscriptions = [];
        foreach ($this->_storeManager->getWebsites() as $website) {
            /** Skip websites without stores */
            if ($website->getStoresCount() === 0) {
                continue;
            }
            $websiteId = (int)$website->getId();
            $subscriptions[$websiteId] = $this->retrieveSubscriberData($customer, $websiteId);
        }

        return $subscriptions;
    }

    /**
     * Retrieve subscriber data
     *
     * @param CustomerInterface $customer
     * @param int $websiteId
     * @return array
     */
    private function retrieveSubscriberData(CustomerInterface $customer, int $websiteId): array
    {
        $subscriber = $this->_subscriberFactory->create()->loadByCustomer((int)$customer->getId(), $websiteId);
        $storeOptions = $this->systemStore->getStoreOptionsTree(false, [], [], [$websiteId]);
        $subscriberData = $subscriber->getData();
        $subscriberData['last_updated'] = $this->getSubscriberStatusChangeDate($subscriber);
        $subscriberData['website_id'] = $websiteId;
        $subscriberData['website_name'] = $this->systemStore->getWebsiteName($websiteId);
        $subscriberData['status'] = $subscriber->isSubscribed();
        $subscriberData['store_options'] = $storeOptions;

        return $subscriberData;
    }

    /**
     * Is single systemStore mode
     *
     * @return bool
     */
    private function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Is single website mode
     *
     * @return bool
     */
    private function isSingleWebsiteMode(): bool
    {
        return $this->isSingleStoreMode()
            || !$this->shareConfig->isGlobalScope()
            || count($this->_storeManager->getWebsites()) === 1;
    }

    /**
     * Update form elements from session data
     *
     * @param Form $form
     * @param int $customerId
     * @return void
     */
    protected function updateFromSession(Form $form, $customerId)
    {
        if (!$this->isSingleWebsiteMode()) {
            return;
        }
        $data = $this->_backendSession->getCustomerFormData();
        $sessionCustomerId = $data['customer']['entity_id'] ?? null;
        if ($sessionCustomerId === null || (int)$sessionCustomerId !== (int)$customerId) {
            return;
        }

        $websiteId = (int)$this->getCurrentCustomer()->getWebsiteId();
        $statusSessionValue = $data['subscription_status'][$websiteId] ?? null;
        if ($statusSessionValue !== null) {
            $subscribeElement = $form->getElement('subscription_status_' . $websiteId);
            $subscribeElement->setValue($statusSessionValue);
            $subscribeElement->setChecked($statusSessionValue);
        }
        $storeSessionValue = $data['subscription_store'][$websiteId] ?? null;
        $storeElement = $form->getElement('subscription_store_' . $websiteId);
        if ($storeSessionValue !== null && $storeElement !== null) {
            $storeElement->setValue($storeSessionValue);
        }
    }

    /**
     * Retrieve the date when the subscriber status changed.
     *
     * @return null|string
     */
    public function getStatusChangedDate()
    {
        $customer = $this->getCurrentCustomer();
        if ($customer === null) {
            return '';
        }
        $customerId = (int)$customer->getId();
        $subscriber = $this->_subscriberFactory->create()->loadByCustomer($customerId, (int)$customer->getWebsiteId());

        return $this->getSubscriberStatusChangeDate($subscriber);
    }

    /**
     * Retrieve the date when the subscriber status changed
     *
     * @param Subscriber $subscriber
     * @return string
     */
    private function getSubscriberStatusChangeDate(Subscriber $subscriber): string
    {
        if (empty($subscriber->getChangeStatusAt())) {
            return '';
        }

        return $this->formatDate(
            $subscriber->getChangeStatusAt(),
            \IntlDateFormatter::MEDIUM,
            true
        );
    }
}
