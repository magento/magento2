<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

/**
 * Price persistence.
 * @since 2.2.0
 */
class PricePersistence
{
    /**
     * Price storage table.
     *
     * @var string
     * @since 2.2.0
     */
    private $table = 'catalog_product_entity_decimal';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     * @since 2.2.0
     */
    private $attributeResource;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     * @since 2.2.0
     */
    private $attributeRepository;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface
     * @since 2.2.0
     */
    private $productIdLocator;

    /**
     * Metadata pool.
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * Attribute code.
     *
     * @var string
     * @since 2.2.0
     */
    private $attributeCode;

    /**
     * Attribute ID.
     *
     * @var int
     * @since 2.2.0
     */
    private $attributeId;

    /**
     * Items per operation.
     *
     * @var int
     * @since 2.2.0
     */
    private $itemsPerOperation = 500;

    /**
     * PricePersistence constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param string $attributeCode
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $attributeCode = ''
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeRepository = $attributeRepository;
        $this->attributeCode = $attributeCode;
        $this->productIdLocator = $productIdLocator;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Get prices by SKUs.
     *
     * @param array $skus
     * @return array
     * @since 2.2.0
     */
    public function get(array $skus)
    {
        $ids = $this->retrieveAffectedIds($skus);
        $select = $this->attributeResource->getConnection()
            ->select()
            ->from($this->attributeResource->getTable($this->table));
        return $this->attributeResource->getConnection()->fetchAll(
            $select->where($this->getEntityLinkField() . ' IN (?)', $ids)
                ->where('attribute_id = ?', $this->getAttributeId())
        );
    }

    /**
     * Update prices.
     *
     * @param array $prices
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 2.2.0
     */
    public function update(array $prices)
    {
        array_walk($prices, function (&$price) {
            return $price['attribute_id'] = $this->getAttributeId();
        });
        $connection = $this->attributeResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($prices, $this->itemsPerOperation) as $pricesBunch) {
                $this->attributeResource->getConnection()->insertOnDuplicate(
                    $this->attributeResource->getTable($this->table),
                    $pricesBunch,
                    ['value']
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save Prices.'),
                $e
            );
        }
    }

    /**
     * Delete product attribute by SKU.
     *
     * @param array $skus
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @since 2.2.0
     */
    public function delete(array $skus)
    {
        $ids = $this->retrieveAffectedIds($skus);
        $connection = $this->attributeResource->getConnection();
        $connection->beginTransaction();
        try {
            foreach (array_chunk($ids, $this->itemsPerOperation) as $idsBunch) {
                $this->attributeResource->getConnection()->delete(
                    $this->attributeResource->getTable($this->table),
                    [
                        'attribute_id = ?' => $this->getAttributeId(),
                        $this->getEntityLinkField() . ' IN (?)' => $idsBunch
                    ]
                );
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete Prices'),
                $e
            );
        }
    }

    /**
     * Retrieve SKU by product ID.
     *
     * @param int $id
     * @param array $skus
     * @return int|null
     * @since 2.2.0
     */
    public function retrieveSkuById($id, $skus)
    {
        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $sku => $ids) {
            if (false !== array_key_exists($id, $ids)) {
                return $sku;
            }
        }

        return null;
    }

    /**
     * Get attribute ID.
     *
     * @return int
     * @since 2.2.0
     */
    private function getAttributeId()
    {
        if (!$this->attributeId) {
            $this->attributeId = $this->attributeRepository->get($this->attributeCode)->getAttributeId();
        }

        return $this->attributeId;
    }

    /**
     * Retrieve affected product IDs.
     *
     * @param array $skus
     * @return array
     * @since 2.2.0
     */
    private function retrieveAffectedIds(array $skus)
    {
        $affectedIds = [];

        foreach ($this->productIdLocator->retrieveProductIdsBySkus($skus) as $productIds) {
            $affectedIds = array_merge($affectedIds, array_keys($productIds));
        }

        return array_unique($affectedIds);
    }

    /**
     * Get link field.
     *
     * @return string
     * @since 2.2.0
     */
    public function getEntityLinkField()
    {
        return $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();
    }
}
