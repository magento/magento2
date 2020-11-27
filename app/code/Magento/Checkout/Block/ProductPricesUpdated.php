<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/**
 * Product price updated time.
 *
 * @api
 */
class ProductPricesUpdated extends Template implements IdentityInterface
{
    public const CACHE_TAG = 'products-updated-at';

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }
}
