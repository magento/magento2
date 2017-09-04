<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

/**
 * Generate specified amount of bundle products based on passed fixture
 *
 * See ProductGenerator for fixture arguments
 * Fixture must return some specific options for generate bundle product:
 * [
 *      '_bundle_variation_sku_pattern' => simple product sku pattern, which will be used as configurable variation,
 *      '_bundle_options' => amount of options per bundle product,
 *      '_bundle_products_per_option' => amount of simple products per each option,
 * ]
 * @see ProductGenerator
 * @see BundleProductTemplateGenerator
 */
class BundleProductGenerator
{
    /**
     * @var ProductGeneratorFactory
     */
    private $productGeneratorFactory;

    /**
     * @param ProductGeneratorFactory $productGeneratorFactory
     */
    public function __construct(ProductGeneratorFactory $productGeneratorFactory)
    {
        $this->productGeneratorFactory = $productGeneratorFactory;
    }

    /**
     * Generate bundle products products
     *
     * @param int $products
     * @param array $fixtureMap
     * @return void
     */
    public function generate($products, $fixtureMap)
    {
        $this->productGeneratorFactory->create([
            'customTableMap' => [
                'catalog_product_bundle_option_value' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['option_id'] = $this->generateOptionId($bind['option_id'], $entityNumber, $fixture);
                        }
                        return $binds;
                    },
                ],
                'catalog_product_bundle_selection' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['option_id'] = $this->generateOptionId($bind['option_id'], $entityNumber, $fixture);
                            $bind['parent_product_id'] = $productId;
                            $simpleProductId = $this->generateSimpleProductId(
                                $bind['product_id'],
                                $entityNumber,
                                $fixture
                            );
                            $bind['product_id'] = $simpleProductId;
                            $bind['selection_price_value'] = $fixture['price']($simpleProductId);
                            $bind['selection_price_type'] = $fixture['priceType']($simpleProductId);
                        }
                        return $binds;
                    },
                ],
                'catalog_product_relation' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['parent_id'] = $productId;
                            $bind['child_id'] = $this->generateSimpleProductId(
                                $bind['child_id'],
                                $entityNumber,
                                $fixture
                            );
                        }
                        return $binds;
                    },
                ],
            ]
        ])->generate($products, $fixtureMap);
    }

    /**
     * Generate value of option_id for $entityNumber bundle product based on previous option_id
     *
     * @param int $previousOptionId
     * @param int $entityNumber
     * @param array $fixture
     * @return int
     */
    private function generateOptionId($previousOptionId, $entityNumber, array $fixture)
    {
        return $previousOptionId + $entityNumber * $fixture['_bundle_options'] + $fixture['_bundle_options'];
    }

    /**
     * Generate value of simple product id which is used for $entityNumber bundle product as option item
     *
     * @param int $previousProductId
     * @param int $entityNumber
     * @param array $fixture
     * @return mixed
     */
    private function generateSimpleProductId($previousProductId, $entityNumber, array $fixture)
    {
        return $previousProductId +
            $entityNumber * $fixture['_bundle_products_per_option'] * $fixture['_bundle_options'];
    }
}
