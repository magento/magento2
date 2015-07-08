<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Framework\View\Element\Template;

class Registration extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Registration $registration,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->registration = $registration;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current email address
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->checkoutSession->getLastRealOrder()->getCustomerEmail();
    }

    /**
     * Retrieve account creation url
     *
     * @return string
     */
    public function getCreateAccountUrl()
    {
        return $this->getUrl('checkout/account/create');
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if ($this->customerSession->isLoggedIn() || !$this->registration->isAllowed()) {
            return '';
        }
        return parent::toHtml();
    }
}
