<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Plugin;

/**
 * Class \Magento\Persistent\Model\Plugin\CustomerData
 *
 * @since 2.1.0
 */
class CustomerData
{
    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.1.0
     */
    protected $persistentData;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.1.0
     */
    protected $customerSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.1.0
     */
    protected $persistentSession;

    /**
     * CustomerData constructor.
     *
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Persistent\Helper\Session $persistentSession
    ) {
        $this->persistentData = $persistentData;
        $this->customerSession = $customerSession;
        $this->persistentSession = $persistentSession;
    }

    /**
     * Reset quote reward point amount
     *
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param \Closure $proceed
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function aroundGetSectionData(
        \Magento\Customer\CustomerData\Customer $subject,
        \Closure $proceed
    ) {
        /** unset customer first name  */
        if (!$this->customerSession->isLoggedIn()
            && $this->persistentData->isEnabled()
            && $this->persistentSession->isPersistent()
        ) {
            return [];
        }
        return $proceed();
    }
}
