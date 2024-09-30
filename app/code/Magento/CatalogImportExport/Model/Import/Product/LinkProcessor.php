<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\LinkFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;

/**
 * Processor for links between products
 *
 * Remark: Via DI it is possible to supply additional link types.
 */
class LinkProcessor
{
    /**
     * @var array
     */
    private $linkNameToId;

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var Helper
     */
    private $resourceHelper;

    /**
     * @var SkuProcessor
     */
    private $skuProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LinkProcessor constructor.
     *
     * @param LinkFactory $linkFactory
     * @param Helper $resourceHelper
     * @param SkuProcessor $skuProcessor
     * @param LoggerInterface $logger
     * @param array $linkNameToId
     */
    public function __construct(
        LinkFactory $linkFactory,
        Helper $resourceHelper,
        SkuProcessor $skuProcessor,
        LoggerInterface $logger,
        array $linkNameToId
    ) {
        $this->linkFactory = $linkFactory;
        $this->resourceHelper = $resourceHelper;
        $this->skuProcessor = $skuProcessor;
        $this->logger = $logger;

        $this->linkNameToId = $linkNameToId;
    }

    /**
     * Gather and save information about product links.
     *
     * Must be called after ALL products saving done.
     *
     * @param Product $importEntity
     * @param Data $dataSourceModel
     * @param string $linkField
     * @return void
     * @throws LocalizedException
     */
    public function saveLinks(
        Product $importEntity,
        Data $dataSourceModel,
        string $linkField
    ): void {
        $resource = $this->linkFactory->create();
        $mainTable = $resource->getMainTable();
        $positionAttrId = [];

        // pre-load 'position' attributes ID for each link type once
        foreach ($this->linkNameToId as $linkId) {
            $select = $importEntity->getConnection()->select()->from(
                $resource->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $importEntity->getConnection()->fetchOne($select, $bind);
        }
        while ($bunch = $dataSourceModel->getNextBunch()) {
            $nextLinkId = $this->resourceHelper->getNextAutoincrement($mainTable);
            $this->processLinkBunches($importEntity, $linkField, $bunch, $resource, $nextLinkId, $positionAttrId);
        }
    }

    /**
     * Add link types (exists for backwards compatibility)
     *
     * @deprecated 101.1.0 Use DI to inject to the constructor
     * @param array $nameToIds
     */
    public function addNameToIds(array $nameToIds): void
    {
        $this->linkNameToId = array_merge($nameToIds, $this->linkNameToId);
    }

    /**
     * Processes link bunches
     *
     * @param Product $importEntity
     * @param string $linkField
     * @param array $bunch
     * @param Link $resource
     * @param int $nextLinkId
     * @param array $positionAttrId
     *
     * @return void
     * @throws LocalizedException
     */
    private function processLinkBunches(
        Product $importEntity,
        string $linkField,
        array $bunch,
        Link $resource,
        int $nextLinkId,
        array $positionAttrId
    ): void {
        $productIds = [];
        $linkRows = [];
        $positionRows = [];
        $linksToDelete = [];

        $bunch = array_filter($bunch, [$importEntity, 'isRowAllowedToImport'], ARRAY_FILTER_USE_BOTH);
        foreach ($bunch as $rowData) {
            $sku = $rowData[Product::COL_SKU];
            $productId = $this->skuProcessor->getNewSku($sku)[$linkField];
            $productIds[] = $productId;
            $productLinkKeys = $this->fetchProductLinks($importEntity, $resource, $productId);
            $linkNameToId = $this->filterProvidedLinkTypes($rowData);

            foreach ($linkNameToId as $linkName => $linkId) {
                $linkSkus = explode($importEntity->getMultipleValueSeparator(), $rowData[$linkName . 'sku']);

                //process empty value
                if (!empty($linkSkus[0]) && $linkSkus[0] === $importEntity->getEmptyAttributeValueConstant()) {
                    $linksToDelete[$linkId][] = $productId;
                    continue;
                }

                $linkPositions = ! empty($rowData[$linkName . 'position'])
                    ? explode($importEntity->getMultipleValueSeparator(), $rowData[$linkName . 'position'])
                    : [];

                $linkSkus = $this->filterValidLinks($importEntity, $sku, $linkSkus);

                foreach ($linkSkus as $linkedKey => $linkedSku) {
                    $linkedId = $this->getProductLinkedId($importEntity, $linkedSku);
                    if ($linkedId == null) {
                        // Import file links to a SKU which is skipped for some reason, which leads to a "NULL"
                        // link causing fatal errors.
                        $formatStr = 'WARNING: Orphaned link skipped: From SKU %s (ID %d) to SKU %s, Link type id: %d';
                        $exception = new \Exception(sprintf($formatStr, $sku, $productId, $linkedSku, $linkId));
                        $this->logger->critical($exception);
                        continue;
                    }
                    $linkKey = $this->composeLinkKey($productId, $linkedId, $linkId);
                    $productLinkKeys[$linkKey] = $productLinkKeys[$linkKey] ?? $nextLinkId;

                    $linkRows[$linkKey] = $linkRows[$linkKey] ?? [
                            'link_id' => $productLinkKeys[$linkKey],
                            'product_id' => $productId,
                            'linked_product_id' => $linkedId,
                            'link_type_id' => $linkId,
                        ];

                    if (! empty($linkPositions[$linkedKey])) {
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

        $this->deleteProductsLinks($importEntity, $resource, $linksToDelete);
        $this->saveLinksData($importEntity, $resource, $productIds, $linkRows, $positionRows);
    }

    /**
     * Delete links
     *
     * @param Product $importEntity
     * @param Link $resource
     * @param array $linksToDelete
     * @return void
     * @throws LocalizedException
     */
    private function deleteProductsLinks(
        Product $importEntity,
        Link $resource,
        array $linksToDelete
    ) {
        if (!empty($linksToDelete) && Import::BEHAVIOR_APPEND === $importEntity->getBehavior()) {
            foreach ($linksToDelete as $linkTypeId => $productIds) {
                if (!empty($productIds)) {
                    $whereLinkId = $importEntity->getConnection()->quoteInto('link_type_id = ?', $linkTypeId);
                    $whereProductId =  $importEntity->getConnection()->quoteInto(
                        'product_id IN (?)',
                        array_unique($productIds)
                    );
                    $importEntity->getConnection()->delete(
                        $resource->getMainTable(),
                        $whereLinkId . ' AND ' . $whereProductId
                    );
                }
            }
        }
    }

    /**
     * Check if product exists for specified SKU
     *
     * @param Product $importEntity
     * @param string $sku
     * @return bool
     */
    private function isSkuExist(Product $importEntity, string $sku): bool
    {
        $sku = strtolower($sku);
        return isset($importEntity->getOldSku()[$sku]);
    }

    /**
     * Get existing SKU record
     *
     * @param Product $importEntity
     * @param string $sku
     * @return mixed
     */
    private function getExistingSku(Product $importEntity, string $sku)
    {
        $sku = strtolower($sku);
        return $importEntity->getOldSku()[$sku];
    }

    /**
     * Fetches Product Links
     *
     * @param Product $importEntity
     * @param Link $resource
     * @param int $productId
     *
     * @return array
     */
    private function fetchProductLinks(Product $importEntity, Link $resource, int $productId): array
    {
        $productLinkKeys = [];
        $select = $importEntity->getConnection()->select()->from(
            $resource->getTable('catalog_product_link'),
            ['id' => 'link_id', 'linked_id' => 'linked_product_id', 'link_type_id' => 'link_type_id']
        )->where(
            'product_id = :product_id'
        );
        $bind = [':product_id' => $productId];
        foreach ($importEntity->getConnection()->fetchAll($select, $bind) as $linkData) {
            $linkKey = $this->composeLinkKey($productId, $linkData['linked_id'], $linkData['link_type_id']);
            $productLinkKeys[$linkKey] = $linkData['id'];
        }

        return $productLinkKeys;
    }

    /**
     * Gets the Id of the Sku
     *
     * @param Product $importEntity
     * @param string $linkedSku
     * @return int|null
     */
    private function getProductLinkedId(Product $importEntity, string $linkedSku): ?int
    {
        $linkedSku = trim($linkedSku);
        $newSku = $this->skuProcessor->getNewSku($linkedSku);

        $linkedId = ! empty($newSku) ?
            $newSku['entity_id'] :
            $this->getExistingSku($importEntity, $linkedSku)['entity_id'];

        return $linkedId;
    }

    /**
     * Saves information about product links
     *
     * @param Product $importEntity
     * @param Link $resource
     * @param array $productIds
     * @param array $linkRows
     * @param array $positionRows
     *
     * @throws LocalizedException
     */
    private function saveLinksData(
        Product $importEntity,
        Link $resource,
        array $productIds,
        array $linkRows,
        array $positionRows
    ) {
        $mainTable = $resource->getMainTable();
        if (Import::BEHAVIOR_APPEND != $importEntity->getBehavior() && $productIds) {
            $importEntity->getConnection()->delete(
                $mainTable,
                $importEntity->getConnection()->quoteInto('product_id IN (?)', array_unique($productIds))
            );
        }
        if ($linkRows) {
            $importEntity->getConnection()->insertOnDuplicate($mainTable, $linkRows, ['link_id']);
        }
        if ($positionRows) {
            // process linked product positions
            $importEntity->getConnection()->insertOnDuplicate(
                $resource->getAttributeTypeTable('int'),
                $positionRows,
                ['value']
            );
        }
    }

    /**
     * Composes the link key
     *
     * @param int $productId
     * @param int $linkedId
     * @param int $linkTypeId
     *
     * @return string
     */
    private function composeLinkKey(int $productId, int $linkedId, int $linkTypeId): string
    {
        return "{$productId}-{$linkedId}-{$linkTypeId}";
    }

    /**
     * Filter out link types which are not provided in the rowData
     *
     * @param array $rowData
     * @return array
     */
    private function filterProvidedLinkTypes(array $rowData)
    {
        return array_filter(
            $this->linkNameToId,
            function ($linkName) use ($rowData) {
                return isset($rowData[$linkName . 'sku']);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Filter out invalid links
     *
     * @param Product $importEntity
     * @param string $sku
     * @param array $linkSkus
     * @return array
     */
    private function filterValidLinks(Product $importEntity, string $sku, array $linkSkus)
    {
        return array_filter(
            $linkSkus,
            function ($linkedSku) use ($sku, $importEntity) {
                $linkedSku = trim($linkedSku);

                return (
                        $this->skuProcessor->getNewSku($linkedSku) !== null
                        || $this->isSkuExist($importEntity, $linkedSku)
                    )
                    && strcasecmp($linkedSku, $sku) !== 0;
            }
        );
    }
}
