<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProductGraphQl\Plugin\Model\Resolver;

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductImage as Subject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item;
use Magento\StoreGraphQl\Model\Resolver\Store\StoreConfigDataProvider;

class ProductImagePlugin
{
    private const CONF_GROUPED_PRODUCT_IMAGE = 'grouped_product_image';

    private const CONF_GROUPED_PRODUCT_IMAGE_PARENT = 'parent';

    private const FIELD_THUMBNAIL = 'thumbnail';

    private const PRODUCT_TYPE_SIMPLE = 'simple';

    private const PRODUCT_TYPE_GROUPED = 'grouped';

    /**
     * ProductImagePlugin Constructor
     *
     * @param StoreConfigDataProvider $storeConfigDataProvider
     */
    public function __construct(
        private readonly StoreConfigDataProvider $storeConfigDataProvider,
    ) {
    }

    /**
     * Update product thumbnail URL to parent product's thumbnail URL for grouped product
     *
     * @param Subject $subject
     * @param array $returnArray
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        Subject $subject,
        array $returnArray,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {

        /* @var $cartItem Item */
        $cartItem = $value['cart_item'] ?? [];
        if (!$cartItem instanceof Item ||
            !isset($cartItem['product_type']) ||
            $cartItem['product_type'] !== self::PRODUCT_TYPE_SIMPLE ||
            $field->getName() !== self::FIELD_THUMBNAIL) {
            return $returnArray;
        }

        $storeConfigData = $this->storeConfigDataProvider->getStoreConfigData(
            $context->getExtensionAttributes()->getStore()
        );
        if ($storeConfigData[self::CONF_GROUPED_PRODUCT_IMAGE] !== self::CONF_GROUPED_PRODUCT_IMAGE_PARENT) {
            return $returnArray;
        }

        return $this->updateThumbnailToParentThumbnail($cartItem, $returnArray);
    }

    /**
     * Update thumbnail URL to parent thumbnail URL
     *
     * @param Item $cartItem
     * @param array $returnArray
     * @return array
     */
    private function updateThumbnailToParentThumbnail(Item $cartItem, array $returnArray): array
    {
        foreach ($cartItem->getOptions() as $option) {
            $parentProduct = $option->getProduct();

            if ($parentProduct->getTypeId() === self::PRODUCT_TYPE_GROUPED && $parentProduct->getThumbnail()) {
                $returnArray['model']['thumbnail'] = $parentProduct->getThumbnail();
                break;
            }
        }

        return $returnArray;
    }
}
