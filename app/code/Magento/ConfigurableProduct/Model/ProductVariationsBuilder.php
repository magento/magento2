<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;

/**
 * Class \Magento\ConfigurableProduct\Model\ProductVariationsBuilder
 *
 * @since 2.0.0
 */
class ProductVariationsBuilder
{
    /**
     * @var \Magento\Framework\Api\AttributeValueFactory
     * @since 2.0.0
     */
    private $customAttributeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix
     * @since 2.0.0
     */
    private $variationMatrix;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param Product\Type\VariationMatrix $variationMatrix
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix $variationMatrix
    ) {
        $this->productFactory = $productFactory;
        $this->customAttributeFactory = $customAttributeFactory;
        $this->variationMatrix = $variationMatrix;
    }

    /**
     * Populate product with variation of attributes
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $attributes
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     * @since 2.0.0
     */
    public function create(\Magento\Catalog\Api\Data\ProductInterface $product, $attributes)
    {
        $variations = $this->variationMatrix->getVariations($attributes);
        $products = [];
        foreach ($variations as $variation) {
            $price = $product->getPrice();
            /** @var \Magento\Catalog\Model\Product $item */
            $item = $this->productFactory->create();
            $item->setData($product->getData());

            $suffix = '';
            foreach ($variation as $attributeId => $valueInfo) {
                $suffix .= '-' . $valueInfo['value'];
                $customAttribute = $this->customAttributeFactory->create()
                    ->setAttributeCode($attributes[$attributeId]['attribute_code'])
                    ->setValue($valueInfo['value']);
                $customAttributes = array_merge(
                    $item->getCustomAttributes(),
                    [
                        $attributes[$attributeId]['attribute_code'] => $customAttribute
                    ]
                );
                $item->setData('custom_attributes', $customAttributes);
            }
            $item->setPrice($price);
            $item->setName($product->getName() . $suffix);
            $item->setSku($product->getSku() . $suffix);
            $item->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
            $products[] = $item;
        }
        return $products;
    }
}
