<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Imported product status state manager
 */
class StatusProcessor
{
    private const ATTRIBUTE_CODE = 'status';
    /**
     * @var array
     */
    private $oldData;
    /**
     * @var array
     */
    private $newData;
    /**
     * @var ResourceModelFactory
     */
    private $resourceFactory;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var MetadataPool
     */
    private $metadataPool;
    /**
     * @var string
     */
    private $productEntityLinkField;
    /**
     * @var AbstractAttribute
     */
    private $attribute;

    /**
     * Initializes dependencies.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceModelFactory $resourceFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceModelFactory $resourceFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->oldData = [];
        $this->newData = [];
        $this->resourceFactory = $resourceFactory;
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Check if status has changed for given (sku, storeId)
     *
     * @param string $sku
     * @param int $storeId
     * @return bool
     */
    public function isStatusChanged(string $sku, int $storeId): bool
    {
        $sku = strtolower($sku);
        if (!isset($this->newData[$sku][$storeId])) {
            $changed = false;
        } elseif (!isset($this->oldData[$sku][$storeId])) {
            $changed = true;
        } else {
            $oldStatus = (int) $this->oldData[$sku][$storeId];
            $newStatus = (int) $this->newData[$sku][$storeId];
            $changed = $oldStatus !== $newStatus;
        }
        return $changed;
    }

    /**
     * Load old status data
     *
     * @param array $linkIdBySku
     */
    public function loadOldStatus(array $linkIdBySku): void
    {
        $connection = $this->resourceConnection->getConnection();
        $linkId = $this->getProductEntityLinkField();
        $select = $connection->select()
            ->from($this->getAttribute()->getBackend()->getTable())
            ->columns([$linkId, 'store_id', 'value'])
            ->where(sprintf('%s IN (?)', $linkId), array_values($linkIdBySku));
        $skuByLinkId = array_flip($linkIdBySku);

        foreach ($connection->fetchAll($select) as $item) {
            if (isset($skuByLinkId[$item[$linkId]])) {
                $this->oldData[$skuByLinkId[$item[$linkId]]][$item['store_id']] = $item['value'];
            }
        }
    }

    /**
     * Set SKU status for given storeId
     *
     * @param string $sku
     * @param string $storeId
     * @param int $value
     */
    public function setStatus(string $sku, string $storeId, int $value): void
    {
        $sku = strtolower($sku);
        $this->newData[$sku][$storeId] = $value;
    }

    /**
     * Get product entity link field.
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        }

        return $this->productEntityLinkField;
    }

    /**
     * Get Attribute model
     *
     * @return AbstractAttribute
     */
    private function getAttribute(): AbstractAttribute
    {
        if ($this->attribute === null) {
            $this->attribute = $this->resourceFactory->create()->getAttribute(self::ATTRIBUTE_CODE);
        }
        return $this->attribute;
    }
}
