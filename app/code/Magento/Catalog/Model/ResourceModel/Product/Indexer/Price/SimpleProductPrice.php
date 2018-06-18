<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Framework\Indexer\DimensionalIndexerInterface;

/**
 * Simple Product Type Price Indexer
 */
class SimpleProductPrice implements DimensionalIndexerInterface
{
    /**
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var string
     */
    private $productType;

    /**
     * @var PriceModifierInterface[]
     */
    private $priceModifiers;

    /**
     * @param BaseFinalPrice $baseFinalPrice
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param string $productType
     * @param array $priceModifiers
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        $productType = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
        array $priceModifiers = []
    ) {
        $this->baseFinalPrice = $baseFinalPrice;
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->productType = $productType;
        $this->priceModifiers = $priceModifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function executeByDimension(array $dimensions, \Traversable $entityIds = null)
    {
        $this->tableMaintainer->createMainTmpTable($dimensions);

        $temporaryPriceTable = $this->indexTableStructureFactory->create([
            'tableName' => $this->tableMaintainer->getMainTmpTable($dimensions),
            'entityField' => 'entity_id',
            'customerGroupField' => 'customer_group_id',
            'websiteField' => 'website_id',
            'taxClassField' => 'tax_class_id',
            'originalPriceField' => 'price',
            'finalPriceField' => 'final_price',
            'minPriceField' => 'min_price',
            'maxPriceField' => 'max_price',
            'tierPriceField' => 'tier_price',
        ]);
        $select = $this->baseFinalPrice->getQuery($dimensions, $this->productType, iterator_to_array($entityIds));
        $query = $select->insertFromSelect($temporaryPriceTable->getTableName(), [], false);
        $this->tableMaintainer->getConnection()->query($query);

        $this->applyPriceModifiers($temporaryPriceTable);
    }

    /**
     * Apply price modifiers to temporary price index table
     *
     * @param IndexTableStructure $temporaryPriceTable
     * @return void
     */
    private function applyPriceModifiers(IndexTableStructure $temporaryPriceTable)
    {
        foreach ($this->priceModifiers as $priceModifier) {
            $priceModifier->modifyPrice($temporaryPriceTable);
        }
    }
}
