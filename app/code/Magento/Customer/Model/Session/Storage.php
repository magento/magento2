<?php
/**
 * Customer session storage
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Session;

/**
 * Class \Magento\Customer\Model\Session\Storage
 *
 * @since 2.0.0
 */
class Storage extends \Magento\Framework\Session\Storage
{
    /**
     * @param \Magento\Customer\Model\Config\Share $configShare
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $namespace
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $namespace = 'customer',
        array $data = []
    ) {
        if ($configShare->isWebsiteScope()) {
            $namespace .= '_' . $storeManager->getWebsite()->getCode();
        }
        parent::__construct($namespace, $data);
    }
}
