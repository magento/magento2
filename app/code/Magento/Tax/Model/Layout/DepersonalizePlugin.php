<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Layout;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var array
     */
    protected $defaultTaxShippingAddress;

    /**
     * @var array
     */
    protected $defaultTaxBillingAddress;

    /**
     * @var int
     */
    protected $customerTaxClassId;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Customer\Model\Session $customerSession
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
