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
    protected $_entityModel;

    /**
     * Links attribute name-to-link type ID.
     *
     * @var array
     */
    protected $_linkNameToId = [];

    /**
     * @var LinkFactory
     */
    protected $_linkFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Helper
     */
    protected $_resourceHelper;

    /**
     * @var Data
     */
    protected $_dataSourceModel;

    /**
     * @var SkuProcessor
     */
    protected $skuProcessor;

    /**
     * @var AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    protected $_resource;

    public function __construct(
        ResourceConnection $resourceConnection,
        LinkFactory $linkFactory,
        LoggerInterface $logger,
        Helper $resourceHelper,
        Data $importData,
        SkuProcessor $skuProcessor,
        array $linkNameToId
    ) {
        $this->_linkFactory = $linkFactory;
        $this->_logger = $logger;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->skuProcessor = $skuProcessor;

        // Temporary solution: arrays do not seem to support init_parameter,
        // so we have to parse the constant by ourselves
        $this->_linkNameToId = [];
        foreach($linkNameToId as $key=>$value) {
            $this->_linkNameToId[$key] = constant($value);
        }

        $this->_connection = $resourceConnection->getConnection();
        $this->_resource = $this->_linkFactory->create();
    }

    public function saveLinks($entityModel, $productEntityLinkField)
    {
        $this->_entityModel = $entityModel;
        $this->_productEntityLinkField = $productEntityLinkField;

        $mainTable = $this->_resource->getMainTable();
        $positionAttrId = [];
        $nextLinkId = $this->_resourceHelper->getNextAutoincrement($mainTable);
        $positionAttrId = $this->loadPositionAttributes($positionAttrId);

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $productIds = [];
            $linkRows = [];
            $positionRows = [];

            foreach ($bunch as $rowNum => $rowData) {
                if ( ! $this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $sku = $rowData[Product::COL_SKU];

                $productId = $this->skuProcessor->getNewSku($sku)[$this->_productEntityLinkField];
                $productLinkKeys = $this->fetchExistingLinks($productId);

                foreach ($this->_linkNameToId as $linkName => $linkId) {
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
    protected function checkForEmptyLinkId($linkedId, $sku, $productId, string $linkedSku): bool
    {
        if ($linkedId == null) {
            // Import file links to a SKU which is skipped for some reason,
            // which leads to a "NULL"
            // link causing fatal errors.
            $this->_logger->critical(
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
    protected function isProperLink(string $linkedSku, $sku): bool
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
    protected function fetchExistingLinks(
        $productId
    ): array {
        $productLinkKeys = [];
        $select = $this->_connection->select()->from(
            $this->_resource->getTable('catalog_product_link'),
            ['id' => 'link_id', 'linked_id' => 'linked_product_id', 'link_type_id' => 'link_type_id']
        )->where(
            'product_id = :product_id'
        );
        $bind = [':product_id' => $productId];
        foreach ($this->_connection->fetchAll($select, $bind) as $linkData) {
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
    protected function getLinkPositions($rowData, string $linkName): array
    {
        $positionField = $linkName . 'position';

        return ! empty($rowData[$positionField])
            ? explode($this->_entityModel->getMultipleValueSeparator(), $rowData[$positionField])
            : [];
    }

    /**
     * @param $rowData
     * @param string $linkField
     *
     * @return bool|array
     */
    protected function getLinkSkus($rowData, string $linkName)
    {
        $linkField = $linkName . 'sku';

        if ( ! isset($rowData[$linkField])) {
            return false;
        }

        return explode($this->_entityModel->getMultipleValueSeparator(), $rowData[$linkField]);
    }

    /**
     * pre-load 'position' attributes ID for each link type once
     *
     * @param array $positionAttrId
     *
     * @return array
     */
    protected function loadPositionAttributes(array $positionAttrId): array
    {
        foreach ($this->_linkNameToId as $linkName => $linkId) {
            $select = $this->_connection->select()->from(
                $this->_resource->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $this->_connection->fetchOne($select, $bind);
        }

        return $positionAttrId;
}

    /**
     * @param array $productIds
     */
    protected function deleteExistingLinks(array $productIds): void
    {
        if (Import::BEHAVIOR_APPEND !== $this->_entityModel->getBehavior() && $productIds) {
            $this->_connection->delete(
                $this->_resource->getMainTable(),
                $this->_connection->quoteInto('product_id IN (?)', array_unique($productIds))
            );
        }
    }

    /**
     * @param array $linkRows
     * @param array $positionRows
     */
    protected function insertNewLinks(array $linkRows, array $positionRows): void
    {
        if ($linkRows) {
            $this->_connection->insertOnDuplicate($this->_resource->getMainTable(), $linkRows, ['link_id']);
        }
        if ($positionRows) {
            // process linked product positions
            $this->_connection->insertOnDuplicate(
                $this->_resource->getAttributeTypeTable('int'),
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
    protected function getLinkedId(string $linkedSku)
    {
        $newSku = $this->skuProcessor->getNewSku($linkedSku);
        if ( ! empty($newSku)) {
            $linkedId = $newSku['entity_id'];
        } else {
            $linkedId = $this->getExistingSku($linkedSku)['entity_id'];
        }

        return $linkedId;
}
}
