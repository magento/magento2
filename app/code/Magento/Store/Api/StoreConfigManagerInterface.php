<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Store config manager interface
 *
 * @api
 * @since 2.0.0
 */
interface StoreConfigManagerInterface
{
    /**
     * @param string[] $storeCodes
     * @return \Magento\Store\Api\Data\StoreConfigInterface[]
     * @since 2.0.0
     */
    public function getStoreConfigs(array $storeCodes = null);
}
