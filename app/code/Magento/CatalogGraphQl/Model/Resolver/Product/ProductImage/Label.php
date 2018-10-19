<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\ProductImage;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Returns product's image label
 */
class Label implements ResolverInterface
{
    /**
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductResourceModel $productResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductResourceModel $productResource,
        StoreManagerInterface $storeManager
    ) {
        $this->productResource = $productResource;
        $this->storeManager = $storeManager;
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
        if (!isset($value['image_type'])) {
            throw new LocalizedException(__('"image_type" value should be specified'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];

        $imageLabel = $this->getImageLabel((int)$product->getEntityId(), $value['image_type']);
        return $imageLabel;
    }

    /**
     * @param int $productId
     * @param string $imageType
     * @return string
     */
    private function getImageLabel(int $productId, string $imageType): string
    {
        $storeId = $this->storeManager->getStore()->getId();

        $imageLabel = $this->productResource->getAttributeRawValue($productId, $imageType . '_label', $storeId);
        if (empty($imageLabel)) {
            $imageLabel = $this->productResource->getAttributeRawValue($productId, 'name', $storeId);
        }
        return $imageLabel;
    }
}
