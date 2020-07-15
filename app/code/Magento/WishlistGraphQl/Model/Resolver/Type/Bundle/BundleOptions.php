<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver\Type\Bundle;

use Magento\Wishlist\Model\Product\BundleOptionDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Item;

/**
 * Fetches the selected bundle options
 */
class BundleOptions implements ResolverInterface
{
    /**
     * @var BundleOptionDataProvider
     */
    private $bundleOptionDataProvider;

    /**
     * @param BundleOptionDataProvider $bundleOptionDataProvider
     */
    public function __construct(
        BundleOptionDataProvider $bundleOptionDataProvider
    ) {
        $this->bundleOptionDataProvider = $bundleOptionDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$value['wishlistItemModel'] instanceof Item) {
            throw new LocalizedException(__('"wishlistItemModel" should be a "%instance" instance', [
                'instance' => Item::class
            ]));
        }

        return $this->bundleOptionDataProvider->getData($value['wishlistItemModel']);
    }
}
