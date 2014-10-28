<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableProduct\Service\V1;

use Magento\Catalog\Service\V1\Data\Product;
use Magento\Catalog\Service\V1\Data\ProductBuilder;
use Magento\Catalog\Service\V1\Product\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;

class ReadService implements ReadServiceInterface
{
    /**
     * @var VariationMatrix
     */
    private $variationMatrix;

    /**
     * @var ProductBuilder
     */
    private $productBuilder;

    /**
     * @var Attribute\ReadServiceInterface
     */
    private $attributeReadService;

    /**
     * @param Attribute\ReadServiceInterface $attributeReadService
     * @param ProductBuilder $productBuilder
     * @param VariationMatrix $variationMatrix
     */
    public function __construct(
        Attribute\ReadServiceInterface $attributeReadService,
        ProductBuilder $productBuilder,
        VariationMatrix $variationMatrix
    ) {
        $this->variationMatrix = $variationMatrix;
        $this->productBuilder = $productBuilder;
        $this->attributeReadService = $attributeReadService;
    }

    /**
     * {@inheritdoc}
     */
    public function generateVariation(Product $product, $options)
    {
        $attributes = $this->getAttributesForMatrix($options);
        $variations = $this->variationMatrix->getVariations($attributes);
        $products = $this->populateProductVariation($product, $variations, $attributes);
        return $products;
    }

    /**
     * Prepare attribute info for variation matrix generation
     *
     * @param \Magento\ConfigurableProduct\Service\V1\Data\Option[] $options
     * @return array
     */
    private function getAttributesForMatrix($options)
    {
        $attributes = [];
        foreach ($options as $option) {
            $configurable = $option->__toArray();
            $attribute = $this->attributeReadService->info($option->getAttributeId());
            $configurable['options'] = $attribute->__toArray()['options'];
            $configurable['attribute_code'] = $attribute->getAttributeCode();
            $attributes[$option->getAttributeId()] = $configurable;
        }
        return $attributes;
    }

    /**
     * Populate product with variation of attributes
     *
     * @param Product $product
     * @param array $variations
     * @param array $attributes
     * @return array
     */
    private function populateProductVariation(Product $product, $variations, $attributes)
    {
        $products = [];
        foreach ($variations as $attributeId => $variation) {
            $price = $product->getPrice();
            $this->productBuilder->populate($product);
            $suffix = '';
            foreach ($variation as $attributeId => $valueInfo) {
                $suffix .= '-' . $valueInfo['value'];
                $this->productBuilder->setCustomAttribute(
                    $attributes[$attributeId]['attribute_code'],
                    $valueInfo['value']
                );
                $priceInfo = $valueInfo['price'];
                $price += (!empty($priceInfo['is_percent']) ? $product->getPrice() / 100.0 : 1.0)
                    * $priceInfo['price'];
            }
            $this->productBuilder->setPrice($price);
            $this->productBuilder->setName($product->getName() . $suffix);
            $this->productBuilder->setSku($product->getSku() . $suffix);
            $this->productBuilder->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
            $products[] = $this->productBuilder->create();
        }
        return $products;
    }
}
