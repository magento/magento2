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
 * @since 100.0.2
 */
interface StoreConfigManagerInterface
{
    /**
     * @param string[] $storeCodes
     * @return \Magento\Store\Api\Data\StoreConfigInterface[]
     */
    public function getStoreConfigs(?array $storeCodes = null);
}
