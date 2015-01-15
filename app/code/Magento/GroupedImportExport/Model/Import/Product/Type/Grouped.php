<?php
/**
 * Import entity of grouped product type
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedImportExport\Model\Import\Product\Type;

class Grouped extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_specialAttributes = ['_associated_sku', '_associated_default_qty', '_associated_position'];

    /**
     * Import model behavior
     *
     * @var string
     */
    protected $_behavior;

    /**
     * @var \Magento\ImportExport\Model\ImportFactory
     */
    protected $_importFactory;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param array $params
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\Catalog\Model\Resource\Product\LinkFactory $productLinkFactory
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac,
        array $params,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\Catalog\Model\Resource\Product\LinkFactory $productLinkFactory,
        \Magento\Framework\App\Resource $resource
    ) {
        $this->_importFactory = $importFactory;
        $this->_resource = $resource;
        $this->_productLinkFactory = $productLinkFactory;
        parent::__construct($attrSetColFac, $prodAttrColFac, $params);
    }

    /**
     * Retrieve model behavior
     *
     * @return string
     */
    public function getBehavior()
    {
        if (is_null($this->_behavior)) {
            $this->_behavior = $this->_importFactory->create()->getDataSourceModel()->getBehavior();
        }
        return $this->_behavior;
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function saveData()
    {
        $groupedLinkId = \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED;
        $connection = $this->_resource->getConnection('write');
        $resource = $this->_productLinkFactory->create();
        $mainTable = $resource->getMainTable();
        $relationTable = $resource->getTable('catalog_product_relation');
        $newSku = $this->_entityModel->getNewSku();
        $oldSku = $this->_entityModel->getOldSku();
        $attributes = [];

        // pre-load attributes parameters
        $select = $connection->select()->from(
            $resource->getTable('catalog_product_link_attribute'),
            ['id' => 'product_link_attribute_id', 'code' => 'product_link_attribute_code', 'type' => 'data_type']
        )->where(
            'link_type_id = ?',
            $groupedLinkId
        );
        foreach ($connection->fetchAll($select) as $row) {
            $attributes[$row['code']] = [
                'id' => $row['id'],
                'table' => $resource->getAttributeTypeTable($row['type']),
            ];
        }
        while ($bunch = $this->_entityModel->getNextBunch()) {
            $linksData = [
                'product_ids' => [],
                'links' => [],
                'attr_product_ids' => [],
                'position' => [],
                'qty' => [],
                'relation' => [],
            ];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum) || empty($rowData['_associated_sku'])
                ) {
                    continue;
                }
                if (isset($newSku[$rowData['_associated_sku']])) {
                    $linkedProductId = $newSku[$rowData['_associated_sku']]['entity_id'];
                } elseif (isset($oldSku[$rowData['_associated_sku']])) {
                    $linkedProductId = $oldSku[$rowData['_associated_sku']]['entity_id'];
                } else {
                    continue;
                }
                $scope = $this->_entityModel->getRowScope($rowData);
                if (\Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT == $scope) {
                    $productData = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]];
                } else {
                    $colAttrSet = \Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET;
                    $rowData[$colAttrSet] = $productData['attr_set_code'];
                    $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE] = $productData['type_id'];
                }
                $productId = $productData['entity_id'];

                if ($this->_type != $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_TYPE]) {
                    continue;
                }
                $linksData['product_ids'][$productId] = true;
                $linksData['links'][$productId][$linkedProductId] = $groupedLinkId;
                $linksData['relation'][] = ['parent_id' => $productId, 'child_id' => $linkedProductId];
                $qty = empty($rowData['_associated_default_qty']) ? 0 : $rowData['_associated_default_qty'];
                $pos = empty($rowData['_associated_position']) ? 0 : $rowData['_associated_position'];

                if ($qty || $pos) {
                    $linksData['attr_product_ids'][$productId] = true;
                    if ($pos) {
                        $linksData['position']["{$productId} {$linkedProductId}"] = [
                            'product_link_attribute_id' => $attributes['position']['id'],
                            'value' => $pos,
                        ];
                    }
                    if ($qty) {
                        $linksData['qty']["{$productId} {$linkedProductId}"] = [
                            'product_link_attribute_id' => $attributes['qty']['id'],
                            'value' => $qty,
                        ];
                    }
                }
            }
            // save links and relations
            if ($linksData['product_ids'] &&
                $this->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND
            ) {
                $connection->delete(
                    $mainTable,
                    $connection->quoteInto(
                        'product_id IN (?) AND link_type_id = ' . $groupedLinkId,
                        array_keys($linksData['product_ids'])
                    )
                );
            }
            if ($linksData['links']) {
                $mainData = [];

                foreach ($linksData['links'] as $productId => $linkedData) {
                    foreach ($linkedData as $linkedId => $linkType) {
                        $mainData[] = [
                            'product_id' => $productId,
                            'linked_product_id' => $linkedId,
                            'link_type_id' => $linkType,
                        ];
                    }
                }
                $connection->insertOnDuplicate($mainTable, $mainData);
                $connection->insertOnDuplicate($relationTable, $linksData['relation']);
            }
            // save positions and default quantity
            if ($linksData['attr_product_ids']) {
                $savedData = $connection->fetchPairs(
                    $connection->select()->from(
                        $mainTable,
                        [new \Zend_Db_Expr('CONCAT_WS(" ", product_id, linked_product_id)'), 'link_id']
                    )->where(
                        'product_id IN (?) AND link_type_id = ' . $groupedLinkId,
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
                if ($linksData['position']) {
                    $connection->insertOnDuplicate($attributes['position']['table'], $linksData['position']);
                }
                if ($linksData['qty']) {
                    $connection->insertOnDuplicate($attributes['qty']['table'], $linksData['qty']);
                }
            }
        }
        return $this;
    }
}
