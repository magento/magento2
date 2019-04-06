<?php

namespace Magento\CatalogImportExport\Model\ResourceModel\Product;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\ImportExport\Model\Import;

class Link extends \Magento\Catalog\Model\ResourceModel\Product\Link
{
    /** @var \Magento\ImportExport\Model\ResourceModel\Helper */
    protected $resourceHelper;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Relation $catalogProductRelation,
        $connectionName = null,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
    ) {
        parent::__construct($context, $catalogProductRelation, $connectionName);
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link $resource
     * @param $productId
     *
     * @return array
     */
    public function fetchExistingLinks(
        $productId
    ): array {

        $productLinkKeys = [];
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_link'),
            ['id' => 'link_id', 'linked_id' => 'linked_product_id', 'link_type_id' => 'link_type_id']
        )->where(
            'product_id = :product_id'
        );
        $bind = [':product_id' => $productId];
        foreach ($this->getConnection()->fetchAll($select, $bind) as $linkData) {
            $linkKey = "{$productId}-{$linkData['linked_id']}-{$linkData['link_type_id']}";
            $productLinkKeys[$linkKey] = $linkData['id'];
        }

        return $productLinkKeys;
    }

    /**
     * pre-load 'position' attributes ID for each link type once
     *
     * @param array $linkNameToId
     *
     * @return array
     */
    public function loadPositionAttributes(array $linkNameToId): array
    {
        $positionAttrId = [];

        foreach ($linkNameToId as $linkId) {
            $select = $this->getConnection()->select()->from(
                $this->getTable('catalog_product_link_attribute'),
                ['id' => 'product_link_attribute_id']
            )->where(
                'link_type_id = :link_id AND product_link_attribute_code = :position'
            );
            $bind = [':link_id' => $linkId, ':position' => 'position'];
            $positionAttrId[$linkId] = $this->getConnection()->fetchOne($select, $bind);
        }

        return $positionAttrId;
    }

    /**
     * @param array $productIds
     */
    public function deleteExistingLinks(array $productIds): void
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            $this->getConnection()->quoteInto('product_id IN (?)', array_unique($productIds))
        );
    }

    /**
     * @param array $linkRows
     * @param array $positionRows
     */
    public function insertNewLinks(array $linkRows, array $positionRows): void
    {
        if ($linkRows) {
            $this->getConnection()->insertOnDuplicate($this->getMainTable(), $linkRows, ['link_id']);
        }
        if ($positionRows) {
            // process linked product positions
            $this->getConnection()->insertOnDuplicate(
                $this->getAttributeTypeTable('int'),
                $positionRows,
                ['value']
            );
        }
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNextAutoincrement(): int
    {
        $mainTable = $this->getMainTable();
        $nextLinkId = $this->resourceHelper->getNextAutoincrement($mainTable);

        return $nextLinkId;
    }

}