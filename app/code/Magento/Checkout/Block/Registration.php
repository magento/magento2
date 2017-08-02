<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 2.0.0
 */
class Registration extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Registration
     * @since 2.0.0
     */
    protected $registration;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     * @since 2.0.0
     */
    protected $accountManagement;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     * @since 2.0.0
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Address\Validator
     * @since 2.0.0
     */
    protected $addressValidator;

    /**
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\Address\Validator $addressValidator
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Address\Validator $addressValidator,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->registration = $registration;
        $this->accountManagement = $accountManagement;
        $this->orderRepository = $orderRepository;
        $this->addressValidator = $addressValidator;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current email address
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getEmailAddress()
    {
        return $this->checkoutSession->getLastRealOrder()->getCustomerEmail();
    }

    /**
     * Retrieve account creation url
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getCreateAccountUrl()
    {
        return $this->getUrl('checkout/account/create');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toHtml()
    {
        if ($this->customerSession->isLoggedIn()
            || !$this->registration->isAllowed()
            || !$this->accountManagement->isEmailAvailable($this->getEmailAddress())
            || !$this->validateAddresses()
        ) {
            return '';
        }
        return parent::toHtml();
    }

    /**
     * Validate order addresses
     *
     * @return bool
     * @since 2.0.0
     */
    protected function validateAddresses()
    {
        $order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            $result = $this->addressValidator->validateForCustomer($address);
            if (is_array($result) && !empty($result)) {
                return false;
            }
        }
        return true;
    }
}
