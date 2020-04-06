<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Layout;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Depersonalize customer data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    private $depersonalizeChecker;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var array
     */
    private $defaultTaxShippingAddress;

    /**
     * @var array
     */
    private $defaultTaxBillingAddress;

    /**
     * @var int
     */
    private $customerTaxClassId;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param CustomerSession $customerSession
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        CustomerSession $customerSession
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->customerSession = $customerSession;
    }

    /**
     * Resolve sensitive customer data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function beforeGenerateXml(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->defaultTaxBillingAddress = $this->customerSession->getDefaultTaxBillingAddress();
            $this->defaultTaxShippingAddress = $this->customerSession->getDefaultTaxShippingAddress();
            $this->customerTaxClassId = $this->customerSession->getCustomerTaxClassId();
        }
    }

    /**
     * Change sensitive customer data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->customerSession->setDefaultTaxBillingAddress($this->defaultTaxBillingAddress);
            $this->customerSession->setDefaultTaxShippingAddress($this->defaultTaxShippingAddress);
            $this->customerSession->setCustomerTaxClassId($this->customerTaxClassId);
        }
    }
}
