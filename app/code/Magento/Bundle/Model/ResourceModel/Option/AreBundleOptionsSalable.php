<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Option;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class AreBundleOptionsSalable
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * Check are bundle product options salable
     *
     * @param int $entityId
     * @param int $storeId
     * @return bool
     */
    public function execute(int $entityId, int $storeId): bool
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $connection = $this->resourceConnection->getConnection();
        $optionsSaleabilitySelect = $connection->select()
            ->from(
                ['parent_products' => $this->resourceConnection->getTableName('catalog_product_entity')],
                []
            )->joinInner(
                ['bundle_options' => $this->resourceConnection->getTableName('catalog_product_bundle_option')],
                "bundle_options.parent_id = parent_products.{$linkField}",
                []
            )->joinInner(
                ['bundle_selections' => $this->resourceConnection->getTableName('catalog_product_bundle_selection')],
                'bundle_selections.option_id = bundle_options.option_id',
                []
            )->joinInner(
                ['child_products' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'child_products.entity_id = bundle_selections.product_id',
                []
            )->group(
                ['bundle_options.parent_id', 'bundle_options.option_id']
            )->where(
                'parent_products.entity_id = ?',
                $entityId
            );
        $statusAttr = $this->productAttributeRepository->get(ProductInterface::STATUS);
        $optionsSaleabilitySelect->joinInner(
            ['child_status_global' => $statusAttr->getBackendTable()],
            "child_status_global.{$linkField} = child_products.{$linkField}"
            . " AND child_status_global.attribute_id = {$statusAttr->getAttributeId()}"
            . " AND child_status_global.store_id = 0",
            []
        )->joinLeft(
            ['child_status_store' => $statusAttr->getBackendTable()],
            "child_status_store.{$linkField} = child_products.{$linkField}"
            . " AND child_status_store.attribute_id = {$statusAttr->getAttributeId()}"
            . " AND child_status_store.store_id = {$storeId}",
            []
        );
        $isOptionSalableExpr = new \Zend_Db_Expr(
            sprintf(
                'MAX(IFNULL(child_status_store.value, child_status_global.value) != %s)',
                ProductStatus::STATUS_DISABLED
            )
        );
        $isRequiredOptionUnsalable = $connection->getCheckSql(
            'required = 1 AND ' . $isOptionSalableExpr . ' = 0',
            '1',
            '0'
        );
        $optionsSaleabilitySelect->columns([
            'required' => 'bundle_options.required',
            'is_salable' => $isOptionSalableExpr,
            'is_required_and_unsalable' => $isRequiredOptionUnsalable,
        ]);

        $select = $connection->select()->from(
            $optionsSaleabilitySelect,
            [new \Zend_Db_Expr('(MAX(is_salable) = 1 AND MAX(is_required_and_unsalable) = 0)')]
        );
        $isSalable = $connection->fetchOne($select);

        return (bool) $isSalable;
    }
}
