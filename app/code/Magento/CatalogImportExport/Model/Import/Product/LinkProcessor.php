<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Model\ResourceModel\Product\LinkFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;

class LinkProcessor
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    private $entityModel;

    /**
     * Links attribute name-to-link type ID.
     *
     * @var array
     */
    private $linkNameToId = [];

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Helper
     */
    private $resourceHelper;

    /**
     * @var Data
     */
    private $dataSourceModel;

    /**
     * @var SkuProcessor
     */
    private $skuProcessor;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    private $resource;

    public function __construct(
        ResourceConnection $resourceConnection,
        LinkFactory $linkFactory,
        LoggerInterface $logger,
        Helper $resourceHelper,
        Data $importData,
        SkuProcessor $skuProcessor,
        array $linkNameToId
    ) {
        $this->linkFactory = $linkFactory;
        $this->logger = $logger;
        $this->resourceHelper = $resourceHelper;
        $this->dataSourceModel = $importData;
        $this->skuProcessor = $skuProcessor;

        // Temporary solution: arrays do not seem to support init_parameter,
        // so we have to parse the constant by ourselves
        $this->linkNameToId = [];
        foreach($linkNameToId as $key=>$value) {
            $this->linkNameToId[$key] = constant($value);
        }

        $this->connection = $resourceConnection->getConnection();
        $this->resource = $this->linkFactory->create();
    }

    /**
     * Add additional links mappings
     *
     * Here for the sole reason of BC in the parent class, use DI instead
     *
     * @deprecated
     */
    public function addNameToIds($nameToIds)
    {
       $this->linkNameToId = array_merge($nameToIds, $this->linkNameToId);
    }

    public function saveLinks($entityModel, $productEntityLinkField)
    {
        $this->entityModel = $entityModel;
        $this->_productEntityLinkField = $productEntityLinkField;

        $mainTable = $this->resource->getMainTable();
        $positionAttrId = [];
        $nextLinkId = $this->resourceHelper->getNextAutoincrement($mainTable);
        $positionAttrId = $this->loadPositionAttributes($positionAttrId);

        while ($bunch = $this->dataSourceModel->getNextBunch()) {
            $productIds = [];
            $linkRows = [];
            $positionRows = [];

            foreach ($bunch as $rowNum => $rowData) {
                if ( ! $this->entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $sku = $rowData[Product::COL_SKU];

                $productId = $this->skuProcessor->getNewSku($sku)[$this->_productEntityLinkField];
                $productLinkKeys = $this->fetchExistingLinks($productId);

                foreach ($this->linkNameToId as $linkName => $linkId) {
                    $productIds[] = $productId;

                    $linkSkus = $this->getLinkSkus($rowData, $linkName);
                    if ($linkSkus === false) {
                        continue;
                    }

                    $linkPositions = $this->getLinkPositions($rowData, $linkName);

                    foreach ($linkSkus as $linkedKey => $linkedSku) {
                        $linkedSku = trim($linkedSku); //NOTE: why is trimming happening here and not for all cols in a general place?
                        if ( ! $this->isProperLink($linkedSku, $sku)) {
                            continue;
                        }

                        $linkedId = $this->getLinkedId($linkedSku);

                        if ($this->checkForEmptyLinkId($linkedId, $sku, $productId, $linkedSku)) {
                            continue;
                        }

                        $linkKey = "{$productId}-{$linkedId}-{$linkId}";
                        if (empty($productLinkKeys[$linkKey])) {
                            $productLinkKeys[$linkKey] = $nextLinkId;
                        }
                        if ( ! isset($linkRows[$linkKey])) {
                            $linkRows[$linkKey] = [
                                'link_id' => $productLinkKeys[$linkKey],
                                'product_id' => $productId,
                                'linked_product_id' => $linkedId,
                                'link_type_id' => $linkId,
                            ];
                        }
                        if ( ! empty($linkPositions[$linkedKey])) {
                            $positionRows[] = [
                                'link_id' => $productLinkKeys[$linkKey],
                                'product_link_attribute_id' => $positionAttrId[$linkId],
                                'value' => $linkPositions[$linkedKey],
                            ];
                        }
                        $nextLinkId++;
                    }
                }
            }

            $this->deleteExistingLinks($productIds);
            $this->insertNewLinks($linkRows, $positionRows);
        }

        return $this;
    }

    /**
     * @param $linkId
     * @param $sku
     * @param $productId
     * @param string $linkedSku
     */
    private function checkForEmptyLinkId($linkedId, $sku, $productId, string $linkedSku): bool
    {
        if ($linkedId == null) {
            // Import file links to a SKU which is skipped for some reason,
            // which leads to a "NULL"
            // link causing fatal errors.
            $this->logger->critical(
                new \Exception(
                    sprintf(
                        'WARNING: Orphaned link skipped: From SKU %s (ID %d) to SKU %s, Link type id: %d',
                        $sku,
                        $productId,
                        $linkedSku,
                        $linkedId
                    )
                )
            );

            return true;
        }

        return false;
    }

    /**
     * @param string $linkedSku
     * @param $sku
     *
     * @return bool
     */
    private function isProperLink(string $linkedSku, $sku): bool
    {
        return ($this->skuProcessor->getNewSku($linkedSku) !== null || $this->isSkuExist($linkedSku))
            && strcasecmp($linkedSku, $sku) !== 0;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link $resource
     * @param $productId
     *
     * @return array
     */
    private function fetchExistingLinks(
        $productId
    ): array {
        $productLinkKeys = [];
        $select = $this->connection->select()->from(
            $this->resource->getTable('catalog_product_link'),
            ['id' => 'link_id', 'linked_id' => 'linked_product_id', 'link_type_id' => 'link_type_id']
        )->where(
            'product_id = :product_id'
        );
        $bind = [':product_id' => $productId];
        foreach ($this->connection->fetchAll($select, $bind) as $linkData) {
            $linkKey = "{$productId}-{$linkData['linked_id']}-{$linkData['link_type_id']}";
            $productLinkKeys[$linkKey] = $linkData['id'];
        }

        return $productLinkKeys;
    }

    /**
     * @param $rowData
     * @param string $positionField
     *
     * @return array
     */
    private function getLinkPositions($rowData, string $linkName): array
    {
        $positionField = $linkName . 'position';

        return ! empty($rowData[$positionField])
            ? explode($this->entityModel->getMultipleValueSeparator(), $rowData[$positionField])
            : [];
    }

    /**
     * @param $rowData
     * @param string $linkField
     *
     * @return bool|array
     */
    private function getLinkSkus($rowData, string $linkName)
    {
        $linkField = $linkName . 'sku';

        if ( ! isset($rowData[$linkField])) {
            return false;
        }

        return explode($this->entityModel->getMultipleValueSeparator(), $rowData[$linkField]);
    }

    /**
     * pre-load 'position' attributes ID for each link type once
     *
     * @param array $positionAttrId
     *
     * @return array
     */
    private function loadPositionAttributes(array $positionAttrId): array
    {
        foreach ($this->linkNameToId as $linkName => $linkId) {
            $select = $this->connection->select()->from(
                $this->resource->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $this->connection->fetchOne($select, $bind);
        }

        return $positionAttrId;
}

    /**
     * @param array $productIds
     */
    private function deleteExistingLinks(array $productIds): void
    {
        if (Import::BEHAVIOR_APPEND !== $this->entityModel->getBehavior() && $productIds) {
            $this->connection->delete(
                $this->resource->getMainTable(),
                $this->connection->quoteInto('product_id IN (?)', array_unique($productIds))
            );
        }
    }

    /**
     * @param array $linkRows
     * @param array $positionRows
     */
    private function insertNewLinks(array $linkRows, array $positionRows): void
    {
        if ($linkRows) {
            $this->connection->insertOnDuplicate($this->resource->getMainTable(), $linkRows, ['link_id']);
        }
        if ($positionRows) {
            // process linked product positions
            $this->connection->insertOnDuplicate(
                $this->resource->getAttributeTypeTable('int'),
                $positionRows,
                ['value']
            );
        }
    }

    /**
     * @param string $linkedSku
     *
     * @return mixed
     */
    private function getLinkedId(string $linkedSku)
    {
        $newSku = $this->skuProcessor->getNewSku($linkedSku);
        if ( ! empty($newSku)) {
            $linkedId = $newSku['entity_id'];
        } else {
            $linkedId = $this->entityModel->getExistingSku($linkedSku)['entity_id'];
        }

        return $linkedId;
}
}
