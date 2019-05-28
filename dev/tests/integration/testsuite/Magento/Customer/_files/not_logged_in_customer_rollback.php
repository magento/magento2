<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


/** @var \Magento\Framework\Registry $registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

/** @var \Magento\Store\Model\StoreManager $store */
         $store = $this->objectManager->get(\Magento\Store\Model\StoreManager::class);

/** @var $customer \Magento\Customer\Model\Customer*/
        $customer = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customer->setWebsiteId($store->getDefaultStoreView()->getWebsiteId());
        $customer->loadByEmail('customer@example.com');
        if ($customer->getId()) {
            $customer->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
