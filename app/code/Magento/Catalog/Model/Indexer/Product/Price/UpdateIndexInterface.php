<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Customer\Api\Data\GroupInterface;

/**
 * Defines strategy for updating price index
 *
 * @api
 * @since 102.0.0
 */
interface UpdateIndexInterface
{
    /**
     * Update price index
     *
     * @param GroupInterface $group
     * @param bool $isGroupNew
     * @return void
     * @since 102.0.0
     */
    public function update(GroupInterface $group, $isGroupNew);
}
