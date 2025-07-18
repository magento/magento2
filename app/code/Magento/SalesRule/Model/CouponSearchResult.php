<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\Api\SearchResults;
use Magento\SalesRule\Api\Data\CouponSearchResultInterface;

/**
 * Service Data Object with Coupon search results.
 *
 * @phpcs:ignoreFile
 */
class CouponSearchResult extends SearchResults implements CouponSearchResultInterface
{
    /**
     * @inheritdoc
     */
    public function setItems(?array $items = null)
    {
        return parent::setItems($items);
    }
}
