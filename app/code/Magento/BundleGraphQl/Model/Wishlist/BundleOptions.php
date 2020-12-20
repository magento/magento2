<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Wishlist;

use Magento\Bundle\Model\Product\BundleOptionDataProvider;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
        if (!$value['itemModel'] instanceof ItemInterface) {
            throw new LocalizedException(__('"itemModel" should be a "%instance" instance', [
                'instance' => ItemInterface::class
            ]));
        }

        return $this->bundleOptionDataProvider->getData($value['itemModel']);
    }
}
