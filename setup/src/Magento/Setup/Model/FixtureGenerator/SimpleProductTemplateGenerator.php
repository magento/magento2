<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Simple product template generator. Return newly created simple product for specified attribute set
 * with default values for product attributes
 */
class SimpleProductTemplateGenerator implements TemplateEntityGeneratorInterface
{
    /**
     * @var array
     */
    private $fixture;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param ProductFactory $productFactory
     * @param array $fixture
     */
    public function __construct(ProductFactory $productFactory, array $fixture)
    {
        $this->fixture = $fixture;
        $this->productFactory = $productFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateEntity()
    {
        $attributeSet = $this->fixture['attribute_set_id'];
        $product = $this->getProductTemplate(
            $attributeSet,
            $this->fixture['additional_attributes']($attributeSet, 0, 0)
        );
        $product->save();

        return $product;
    }

    /**
     * Get product template
     *
     * @param int $attributeSet
     * @param array $additionalAttributes
     * @return ProductInterface
     */
    private function getProductTemplate($attributeSet, $additionalAttributes = [])
    {
        $productRandomizerNumber = crc32(mt_rand(1, PHP_INT_MAX));
        $product = $this->productFactory->create([
            'data' => [
                'attribute_set_id' => $attributeSet,
                'type_id' => Type::TYPE_SIMPLE,
                'name' => 'template name' . $productRandomizerNumber,
                'url_key' => 'template-url' . $productRandomizerNumber,
                'sku' => 'template_sku' . $productRandomizerNumber,
                'price' => 10,
                'visibility' => Visibility::VISIBILITY_BOTH,
                'status' => Status::STATUS_ENABLED,
                'website_ids' => (array)$this->fixture['website_ids'](1, 0),
                'category_ids' => isset($this->fixture['category_ids']) ? [2] : null,
                'weight' => 1,
                'description' => 'description',
                'short_description' => 'short description',
                'tax_class_id' => 2, //'taxable goods',
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100500,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1
                ],
            ]
        ]);

        foreach ($additionalAttributes as $attributeCode => $attributeValue) {
            $product->setData($attributeCode, $attributeValue);
        }

        return $product;
    }
}
