<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Model\ResourceModel\Product\LinkFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
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
    protected $_linkNameToId = [
        '_related_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
        '_crosssell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
        '_upsell_' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
    ];

    public function __construct(
        $entityModel,
        AdapterInterface $connection,
        LinkFactory $linkFactory,
        Helper $resourceHelper,
        Data $dateSourceModel,
        SkuProcessor $skuProcessor,
        LoggerInterface $logger,
        string $productEntityLinkField
    ) {
        $this->_entityModel = $entityModel;
        $this->_connection = $connection;
        $this->_linkFactory = $linkFactory;
        $this->_resourceHelper = $resourceHelper; // TODO: inject via DI ? do we need the actual instance from parent or is a new okay?
        $this->_dataSourceModel = $dateSourceModel;
        $this->skuProcessor = $skuProcessor;
        $this->_logger = $logger;
        $this->_productEntityLinkField = $productEntityLinkField;
    }

    public function process()
    {
        $resource = $this->_linkFactory->create();
        $mainTable = $resource->getMainTable();
        $positionAttrId = [];
        $nextLinkId = $this->_resourceHelper->getNextAutoincrement($mainTable);

        // pre-load 'position' attributes ID for each link type once
        foreach ($this->_linkNameToId as $linkName => $linkId) {
            $select = $this->_connection->select()->from(
                $resource->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $this->_connection->fetchOne($select, $bind);
        }
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $productIds = [];
            $linkRows = [];
            $positionRows = [];

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $sku = $rowData[Product::COL_SKU];

                $productId = $this->skuProcessor->getNewSku($sku)[$this->_productEntityLinkField];
                $productLinkKeys = [];
                $select = $this->_connection->select()->from(
                    $resource->getTable('catalog_product_link'),
                    ['id' => 'link_id', 'linked_id' => 'linked_product_id', 'link_type_id' => 'link_type_id']
                )->where(
                    'product_id = :product_id'
                );
                $bind = [':product_id' => $productId];
                foreach ($this->_connection->fetchAll($select, $bind) as $linkData) {
                    $linkKey = "{$productId}-{$linkData['linked_id']}-{$linkData['link_type_id']}";
                    $productLinkKeys[$linkKey] = $linkData['id'];
                }
                foreach ($this->_linkNameToId as $linkName => $linkId) {
                    $productIds[] = $productId;
                    if (isset($rowData[$linkName . 'sku'])) {
                        $linkSkus = explode($this->_entityModel->getMultipleValueSeparator(), $rowData[$linkName . 'sku']);
                        $linkPositions = !empty($rowData[$linkName . 'position'])
                            ? explode($this->_entityModel->getMultipleValueSeparator(), $rowData[$linkName . 'position'])
                            : [];
                        foreach ($linkSkus as $linkedKey => $linkedSku) {
                            $linkedSku = trim($linkedSku);
                            if (($this->skuProcessor->getNewSku($linkedSku) !== null || $this->isSkuExist($linkedSku))
                                && strcasecmp($linkedSku, $sku) !== 0
                            ) {
                                $newSku = $this->skuProcessor->getNewSku($linkedSku);
                                if (!empty($newSku)) {
                                    $linkedId = $newSku['entity_id'];
                                } else {
                                    $linkedId = $this->getExistingSku($linkedSku)['entity_id'];
                                }

                                if ($linkedId == null) {
                                    // Import file links to a SKU which is skipped for some reason,
                                    // which leads to a "NULL"
                                    // link causing fatal errors.
                                    $this->_logger->critical(
                                        new \Exception(
                                            sprintf(
                                                'WARNING: Orphaned link skipped: From SKU %s (ID %d) to SKU %s, ' .
                                                'Link type id: %d',
                                                $sku,
                                                $productId,
                                                $linkedSku,
                                                $linkId
                                            )
                                        )
                                    );
                                    continue;
                                }

                                $linkKey = "{$productId}-{$linkedId}-{$linkId}";
                                if (empty($productLinkKeys[$linkKey])) {
                                    $productLinkKeys[$linkKey] = $nextLinkId;
                                }
                                if (!isset($linkRows[$linkKey])) {
                                    $linkRows[$linkKey] = [
                                        'link_id' => $productLinkKeys[$linkKey],
                                        'product_id' => $productId,
                                        'linked_product_id' => $linkedId,
                                        'link_type_id' => $linkId,
                                    ];
                                }
                                if (!empty($linkPositions[$linkedKey])) {
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
                }
            }
            if (Import::BEHAVIOR_APPEND !== $this->_entityModel->getBehavior() && $productIds) {
                $this->_connection->delete(
                    $mainTable,
                    $this->_connection->quoteInto('product_id IN (?)', array_unique($productIds))
                );
            }
            if ($linkRows) {
                $this->_connection->insertOnDuplicate($mainTable, $linkRows, ['link_id']);
            }
            if ($positionRows) {
                // process linked product positions
                $this->_connection->insertOnDuplicate(
                    $resource->getAttributeTypeTable('int'),
                    $positionRows,
                    ['value']
                );
            }
        }
        return $this;
    }
}
