<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Layout;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 * @since 2.0.0
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     * @since 2.0.0
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $defaultTaxShippingAddress;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $defaultTaxBillingAddress;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $customerTaxClassId;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Customer\Model\Session $customerSession
     * @since 2.0.0
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->depersonalizeChecker = $depersonalizeChecker;
    }

    /**
     * Before generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @return array
     * @since 2.0.0
     */
    public function beforeGenerateXml(\Magento\Framework\View\LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->defaultTaxBillingAddress = $this->customerSession->getDefaultTaxBillingAddress();
            $this->defaultTaxShippingAddress = $this->customerSession->getDefaultTaxShippingAddress();
            $this->customerTaxClassId = $this->customerSession->getCustomerTaxClassId();
        }
        return [];
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->customerSession->setDefaultTaxBillingAddress($this->defaultTaxBillingAddress);
            $this->customerSession->setDefaultTaxShippingAddress($this->defaultTaxShippingAddress);
            $this->customerSession->setCustomerTaxClassId($this->customerTaxClassId);
        }
        return $result;
    }
}
