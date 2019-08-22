<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

/**
 * Generate specified amount of configurable products based on passed fixture
 *
 * See ProductGenerator for fixture arguments
 * Fixture must return some specific options for generate configurable product:
 * [
 *      '_variation_sku_pattern' => simple product sku pattern, which will be used as configurable variation,
 *      '_attributes_count' => amount of attributes on which configurable product is based,
 *      '_variation_count' => amount of generated variations,
 *      '_attributes' => product attributes on which configurable product is based ,
 * ]
 * @see ProductGenerator
 * @see ConfigurableProductTemplateGenerator
 */
class ConfigurableProductGenerator
{
    /**
     * @var ProductGeneratorFactory
     */
    private $productGeneratorFactory;

    /**
     * @var AutoIncrement
     */
    private $autoIncrement;

    /**
     * @param ProductGeneratorFactory $productGeneratorFactory
     * @param AutoIncrement $autoIncrement
     */
    public function __construct(
        ProductGeneratorFactory $productGeneratorFactory,
        AutoIncrement $autoIncrement
    ) {
        $this->productGeneratorFactory = $productGeneratorFactory;
        $this->autoIncrement = $autoIncrement;
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
                'catalog_product_super_attribute_label' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['product_super_attribute_id'] = $this->generateSuperAttributeId(
                                $bind['product_super_attribute_id'],
                                $entityNumber,
                                $fixture
                            );
                        }
                        return $binds;
                    },
                ],
                'catalog_product_super_link' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['parent_id'] = $productId;
                            $bind['product_id'] = $this->generateSimpleProductId(
                                $bind['product_id'],
                                $entityNumber,
                                $fixture
                            );
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
     * @param int $superAttributeId
     * @param int $entityNumber
     * @param array $fixture
     * @return int
     */
    private function generateSuperAttributeId($superAttributeId, $entityNumber, array $fixture)
    {
        return $superAttributeId + ($entityNumber + 1) * $fixture['_attributes_count']
            * $this->autoIncrement->getIncrement();
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
        return $previousProductId + $entityNumber * $fixture['_variation_count'];
    }
}
