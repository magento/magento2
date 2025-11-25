<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;

class BasePrice
{
    /**
     * Price storage table.
     *
     * @var string
     */
    private $table = 'catalog_product_entity_decimal';

    /**
     * @param Attribute $attributeResource
     * @param MetadataPool $metadataPool
     * @param int $attributeId
     */
    public function __construct(
        private readonly Attribute    $attributeResource,
        private readonly MetadataPool $metadataPool,
        private readonly int          $attributeId = 0,
    ) {
    }

    /**
     * Get prices that will need update
     *
     * @param array $priceBunch
     * @return array
     * @throws \Exception
     */
    private function getExistingPrices(array $priceBunch): array
    {
        $linkField = $this->getEntityLinkField();
        $connection = $this->attributeResource->getConnection();

        return $connection->fetchAll(
            $connection->select()
                ->from($this->attributeResource->getTable($this->table))
                ->where('attribute_id = ?', $this->attributeId)
                ->where('store_id IN (?)', array_unique(array_column($priceBunch, 'store_id')))
                ->where($linkField . ' IN (?)', array_unique(array_column($priceBunch, $linkField)))
        );
    }

    /**
     * Get prices that will need update
     *
     * @param DataObject $priceBunchesObject
     * @param array $existingPrices
     * @return array
     * @throws \Exception
     */
    private function getUpdatablePrices(DataObject $priceBunchesObject, array $existingPrices): array
    {
        $updateData = [];
        $priceBunches = $priceBunchesObject->getPrices();
        $linkField = $this->getEntityLinkField();
        foreach ($existingPrices as $existingPrice) {
            foreach ($priceBunches as $key => $price) {
                if ($price[$linkField] == $existingPrice[$linkField] &&
                    $price['store_id'] == $existingPrice['store_id'] &&
                    $existingPrice['attribute_id'] == $price['attribute_id']
                ) {
                    $priceBunches[$key]['value_id'] = $existingPrice['value_id'];
                    $uniqueKey = $price[$linkField].'_'.$price['attribute_id'].'_'.$price['store_id'];
                    $updateData[$uniqueKey] = $priceBunches[$key];
                }
            }
        }

        $priceBunchesObject->setPrices($priceBunches);

        return $updateData;
    }

    /**
     * Get prices that will need insert
     *
     * @param DataObject $priceBunchesObject
     * @return array
     * @throws \Exception
     */
    private function getInsertablePrices(DataObject $priceBunchesObject): array
    {
        $insertData = [];
        $priceBunches = $priceBunchesObject->getPrices();
        $linkField = $this->getEntityLinkField();
        foreach ($priceBunches as $price) {
            if (!isset($price['value_id'])) {
                $uniqueKey = $price[$linkField].'_'.$price['attribute_id'].'_'.$price['store_id'];
                $insertData[$uniqueKey] = $price;
            }
        }
        return $insertData;
    }

    /**
     * Update existing prices
     *
     * @param array $priceBunches
     * @return void
     * @throws \Exception
     */
    public function update(array $priceBunches): void
    {
        $priceBunchesObject = new DataObject(['prices' => $priceBunches]);
        $existingPrices = $this->getExistingPrices($priceBunches);
        $updateData = $this->getUpdatablePrices($priceBunchesObject, $existingPrices);
        $insertData = $this->getInsertablePrices($priceBunchesObject);

        $connection = $this->attributeResource->getConnection();
        $connection->beginTransaction();
        try {
            if (!empty($updateData)) {
                foreach ($updateData as $row) {
                    $this->attributeResource->getConnection()->update(
                        $this->attributeResource->getTable($this->table),
                        ['value' => $row['value']],
                        ['value_id = ?' => (int)$row['value_id']]
                    );
                }
            }
            if (!empty($insertData)) {
                $this->attributeResource->getConnection()->insertMultiple(
                    $this->attributeResource->getTable($this->table),
                    $insertData
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new CouldNotSaveException(
                __('Could not save Prices.'),
                $e
            );
        }
    }

    /**
     * Get link field
     *
     * @return string
     * @throws \Exception
     */
    private function getEntityLinkField(): string
    {
        return $this->metadataPool->getMetadata(ProductInterface::class)
            ->getLinkField();
    }
}
