<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Resolve data for product canonical URL
 */
class CanonicalUrl implements ResolverInterface
{
    /** @var ProductHelper */
    private $productHelper;

    /**
     * @param Product $productHelper
     */
    public function __construct(ProductHelper $productHelper)
    {
        $this->productHelper = $productHelper;
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var Product $product */
        $product = $value['model'];
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        if ($this->productHelper->canUseCanonicalTag($store)) {
            $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
            return $product->getRequestPath();
        }
        return null;
    }
}
