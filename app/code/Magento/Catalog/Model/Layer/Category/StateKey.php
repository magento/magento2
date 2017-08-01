<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Layer\Category;

use Magento\Catalog\Model\Layer\StateKeyInterface;

/**
 * Class \Magento\Catalog\Model\Layer\Category\StateKey
 *
 * @since 2.0.0
 */
class StateKey implements StateKeyInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
    }

    /**
     * Build state key
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     * @since 2.0.0
     */
    public function toString($category)
    {
        return 'STORE_' . $this->storeManager->getStore()->getId()
            . '_CAT_' . $category->getId()
            . '_CUSTGROUP_' . $this->customerSession->getCustomerGroupId();
    }
}
