<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Import\Product\Type\Grouped;

use Magento\Framework\App\ResourceConnection;

/**
 * Processing db operations for import entity of grouped product type
 */
class Links
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    protected $productLink;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    protected $importFactory;

    /**
     * Import model behavior
     *
     * @var string
     */
    protected $behavior;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link $productLink
     * @param ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Link $productLink,
        ResourceConnection $resource,
        \Magento\ImportExport\Model\ImportFactory $importFactory
    ) {
        $this->productLink = $productLink;
        $this->importFactory = $importFactory;
        $this->connection = $resource->getConnection();
    }

    /**
     * @param array $linksData
     * @return void
     */
    public function saveLinksData($linksData)
    {
        $mainTable = $this->productLink->getMainTable();
        $relationTable = $this->productLink->getTable('catalog_product_relation');
        // save links and relations
        if ($linksData['product_ids']) {
            $this->deleteOldLinks(array_keys($linksData['product_ids']));
            $mainData = [];
            foreach ($linksData['relation'] as $productData) {
                $mainData[] = [
                    'product_id' => $productData['parent_id'],
                    'linked_product_id' => $productData['child_id'],
                    'link_type_id' => $this->getLinkTypeId()
                ];
            }
            $this->connection->insertOnDuplicate($mainTable, $mainData);
            $this->connection->insertOnDuplicate($relationTable, $linksData['relation']);
        }

        $attributes = $this->getAttributes();
        // save positions and default quantity
        if ($linksData['attr_product_ids']) {
            $savedData = $this->connection->fetchPairs(
                $this->connection->select()->from(
                    $mainTable,
                    [new \Zend_Db_Expr('CONCAT_WS(" ", product_id, linked_product_id)'), 'link_id']
                )->where(
                    'product_id IN (?) AND link_type_id = ' . $this->connection->quote($this->getLinkTypeId()),
                    array_keys($linksData['attr_product_ids'])
                )
            );
            foreach ($savedData as $pseudoKey => $linkId) {
                if (isset($linksData['position'][$pseudoKey])) {
                    $linksData['position'][$pseudoKey]['link_id'] = $linkId;
                }
                if (isset($linksData['qty'][$pseudoKey])) {
                    $linksData['qty'][$pseudoKey]['link_id'] = $linkId;
                }
            }
            if (!empty($linksData['position'])) {
                $this->connection->insertOnDuplicate($attributes['position']['table'], $linksData['position']);
            }
            if (!empty($linksData['qty'])) {
                $this->connection->insertOnDuplicate($attributes['qty']['table'], $linksData['qty']);
            }
        }
    }

    /**
     * @param array $productIds
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function deleteOldLinks($productIds)
    {
        if ($this->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
            $this->connection->delete(
                $this->productLink->getMainTable(),
                $this->connection->quoteInto(
                    'product_id IN (?) AND link_type_id = ' . $this->getLinkTypeId(),
                    $productIds
                )
            );
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        if (empty($this->attributes)) {
            $select = $this->connection->select()->from(
                $this->productLink->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id', 'code' => 'product_link_attribute_code', 'type' => 'data_type']
            )->where('link_type_id = ?', $this->getLinkTypeId());
            foreach ($this->connection->fetchAll($select) as $row) {
                $this->attributes[$row['code']] = [
                    'id' => $row['id'],
                    'table' => $this->productLink->getAttributeTypeTable($row['type'])
                ];
            }
        }
        return $this->attributes;
    }

    /**
     * @return int
     */
    protected function getLinkTypeId()
    {
        return \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED;
    }

    /**
     * Retrieve model behavior
     *
     * @return string
     */
    protected function getBehavior()
    {
        if ($this->behavior === null) {
            $this->behavior = $this->importFactory->create()->getDataSourceModel()->getBehavior();
        }
        return $this->behavior;
    }
}
