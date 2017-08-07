<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

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
 * @since 2.2.0
 */
class BundleProductGenerator
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $sequenceValues = [
        'sequence_product_bundle_option' => null,
        'sequence_product_bundle_selection' => null
    ];

    /**
     * @var ProductGeneratorFactory
     * @since 2.2.0
     */
    private $productGeneratorFactory;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resource;

    /**
     * @param ProductGeneratorFactory $productGeneratorFactory
     * @param ResourceConnection $resource|null
     * @since 2.2.0
     */
    public function __construct(
        ProductGeneratorFactory $productGeneratorFactory,
        ResourceConnection $resource = null
    ) {
        $this->productGeneratorFactory = $productGeneratorFactory;

        $this->resource = $resource ?: ObjectManager::getInstance()->get(
            ResourceConnection::class
        );
    }

    /**
     * Generates bundle products.
     *
     * @param int $products
     * @param array $fixtureMap
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.2.0
     */
    public function generate($products, $fixtureMap)
    {
        $this->productGeneratorFactory->create([
            'customTableMap' => [
                'catalog_product_bundle_option' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['option_id'] = $this->generateOptionId(
                                $entityNumber,
                                $bind['option_id'],
                                $fixture
                            );

                            $bind['parent_id'] = $productId;
                        }

                        return $binds;
                    },
                ],
                'sequence_product_bundle_option' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['sequence_value'] = $this->generateSequenceId(
                                'sequence_product_bundle_option'
                            );
                        }

                        return $binds;
                    },
                ],
                'catalog_product_bundle_option_value' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['option_id'] = $this->generateOptionId(
                                $entityNumber,
                                $bind['option_id'],
                                $fixture
                            );

                            $bind['parent_product_id'] = $productId;
                        }

                        return $binds;
                    },
                ],
                'catalog_product_bundle_selection' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['selection_id'] = $this->generateSelectionId(
                                $entityNumber,
                                $bind['selection_id'],
                                $fixture
                            );

                            $bind['parent_product_id'] = $productId;

                            $bind['option_id'] = $this->generateOptionId(
                                $entityNumber,
                                $bind['option_id'],
                                $fixture
                            );

                            $bind['product_id'] = $this->generateSimpleProductId(
                                $bind['product_id'],
                                $entityNumber,
                                $fixture
                            );

                            $bind['selection_price_type'] = $fixture['priceType']($bind['product_id']);
                            $bind['selection_price_value'] = $fixture['price']($bind['product_id']);
                        }

                        return $binds;
                    },
                ],
                'sequence_product_bundle_selection' => [
                    'entity_id_field' => EntityGenerator::SKIP_ENTITY_ID_BINDING,
                    'handler' => function ($productId, $entityNumber, $fixture, $binds) {
                        foreach ($binds as &$bind) {
                            $bind['sequence_value'] = $this->generateSequenceId(
                                'sequence_product_bundle_selection'
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
     * Generates an option Id.
     *
     * @param int $entityNumber
     * @param int $originalOptionId
     * @param array $fixture
     *
     * @return int|null
     * @since 2.2.0
     */
    private function generateOptionId($entityNumber, $originalOptionId, array $fixture)
    {
        if ($originalOptionId) {
            return $fixture['_bundle_options'] * ($entityNumber + 1) + $originalOptionId;
        }

        return $originalOptionId;
    }

    /**
     * Generates a selection Id.
     *
     * @param int $entityNumber
     * @param int $originalSelectionId
     * @param array $fixture
     *
     * @return int|null
     * @since 2.2.0
     */
    private function generateSelectionId($entityNumber, $originalSelectionId, array $fixture)
    {
        if ($originalSelectionId) {
            $selectionsPerProduct = $fixture['_bundle_products_per_option'] * $fixture['_bundle_options'];

            return $selectionsPerProduct * ($entityNumber + 1) + $originalSelectionId;
        }

        return $originalSelectionId;
    }

    /**
     * Generates an Id for the given sequence table.
     *
     * @param string $tableName
     *
     * @return int
     * @since 2.2.0
     */
    private function generateSequenceId($tableName)
    {
        if (!$this->sequenceValues[$tableName]) {
            $connection = $this->resource->getConnection();

            $this->sequenceValues[$tableName] = $connection->fetchOne(
                $connection->select()->from(
                    $this->resource->getTableName($tableName),
                    'MAX(`sequence_value`)'
                )
            );
        }

        return ++$this->sequenceValues[$tableName];
    }

    /**
     * Generate value of simple product id which is used for $entityNumber bundle product as option item
     *
     * @param int $previousProductId
     * @param int $entityNumber
     * @param array $fixture
     * @return mixed
     * @since 2.2.0
     */
    private function generateSimpleProductId($previousProductId, $entityNumber, array $fixture)
    {
        return $previousProductId +
            $entityNumber * $fixture['_bundle_products_per_option'] * $fixture['_bundle_options'];
    }
}
