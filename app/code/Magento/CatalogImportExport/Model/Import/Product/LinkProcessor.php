<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;
use Magento\CatalogImportExport\Model\ResourceModel\Product\LinkFactory;

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
     * @var Data
     */
    private $dataSourceModel;

    /**
     * @var SkuProcessor
     */
    private $skuProcessor;

    public function __construct(
        \Magento\CatalogImportExport\Model\ResourceModel\Product\LinkFactory $linkFactory,
        LoggerInterface $logger,
        Data $importData,
        SkuProcessor $skuProcessor,
        array $linkNameToId
    ) {
        $this->linkFactory = $linkFactory;
        $this->logger = $logger;
        $this->dataSourceModel = $importData;
        $this->skuProcessor = $skuProcessor;

        // arrays do not seem to support init_parameter,
        // so we have to parse the constant by ourselves
        $this->linkNameToId = [];
        foreach($linkNameToId as $key=>$value) {
            $this->linkNameToId[$key] = constant($value);
        }

        $this->resource = $this->linkFactory->create(['connectionName' => null]);
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

        $nextLinkId = $this->resource->getNextAutoincrement();

        $positionAttrId = [];
        $positionAttrId = $this->resource->loadPositionAttributes($this->linkNameToId, $positionAttrId);

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
                $productLinkKeys = $this->resource->fetchExistingLinks($productId);

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

            if (Import::BEHAVIOR_APPEND !== $this->entityModel->getBehavior() && $productIds) {
                $this->resource->deleteExistingLinks($productIds);
            }
            $this->resource->insertNewLinks($linkRows, $positionRows);
        }

        return $this;
    }

    /**
     * Check for empty link id, if it is empty the
     * import file links to a SKU which is skipped for some reason,
     * which leads to a "NULL" link causing fatal errors.
     *
     * @param $linkId
     * @param $sku
     * @param $productId
     * @param string $linkedSku
     */
    private function checkForEmptyLinkId($linkedId, $sku, $productId, string $linkedSku): bool
    {
        if ($linkedId == null) {

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
        return ($this->skuProcessor->getNewSku($linkedSku) !== null || $this->_entityModel->isSkuExist($linkedSku))
            && strcasecmp($linkedSku, $sku) !== 0;
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
     * @param string $linkedSku
     *
     * @return mixed
     */
    private function getLinkedId(string $linkedSku)
    {
        $newSku = $this->skuProcessor->getNewSku($linkedSku);
        if ( ! empty($newSku)) {
            return $newSku['entity_id'];
        }

        return $this->entityModel->getExistingSku($linkedSku)['entity_id'];
    }

}
