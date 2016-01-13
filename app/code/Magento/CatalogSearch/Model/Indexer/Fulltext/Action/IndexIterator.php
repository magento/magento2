<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;


class IndexIterator implements \Iterator
{

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\ClassTen
     */
    private $dataProvider;


    /** Arguments */
    private $storeId;
    private $staticFields;
    private $productIds;
    private $dynamicFields;
    private $visibility;
    private $allowedVisibility;
    private $status;
    private $statusIds;


    /** Internal vars */
    private $lastProductId = 0;
    private $products = [];
    private $current = null;
    private $isValid = true;
    private $key = null;
    private $productAttributes = [];
    private $productRelations = [];

    /**
     * Initialize dependencies.
     *
     * @param ClassTen $dataProvider
     * @param $storeId
     * @param $staticFields
     * @param $productIds
     * @param $dynamicFields
     * @param $visibility
     * @param $allowedVisibility
     * @param $status
     * @param $statusIds
     */
    public function __construct(ClassTen $dataProvider, $storeId, $staticFields, $productIds, $dynamicFields, $visibility, $allowedVisibility, $status, $statusIds)
    {
        $this->dataProvider = $dataProvider;
        $this->storeId = $storeId;
        $this->staticFields = $staticFields;
        $this->productIds = $productIds;
        $this->dynamicFields = $dynamicFields;
        $this->visibility = $visibility;
        $this->allowedVisibility = $allowedVisibility;
        $this->status = $status;
        $this->statusIds = $statusIds;
    }


    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        \next($this->products);
        if (\key($this->products) === null) {
            //check if storage has more items to process
            $this->products = $this->dataProvider->getSearchableProducts(
                $this->storeId,
                $this->staticFields,
                $this->productIds,
                $this->lastProductId
            );

            if(!count($this->products)) {
                $this->isValid = false;
                return;
            }


            $productAttributes = [];
            $this->productRelations = [];
            foreach ($this->products as $productData) {
                $this->lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChildren = $this->dataProvider->getProductChildIds($productData['entity_id'], $productData['type_id']);
                $this->productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }

            $this->productAttributes = $this->dataProvider->getProductAttributes($this->storeId, $productAttributes, $this->dynamicFields);
        }

        $productData = \current($this->products);

        if (!isset($this->productAttributes[$productData['entity_id']])) {
            $this->next();
        }

        $productAttr = $this->productAttributes[$productData['entity_id']];
        if (!isset($productAttr[$this->visibility->getId()])
            || !in_array($productAttr[$this->visibility->getId()], $this->allowedVisibility)
        ) {
            $this->next();
        }
        if (!isset($productAttr[$this->status->getId()])
            || !in_array($productAttr[$this->status->getId()], $this->statusIds)
        ) {
            $this->next();
        }

        $productIndex = [$productData['entity_id'] => $productAttr];

        $hasChildren = false;
        $productChildren = $this->productRelations[$productData['entity_id']];
        if ($productChildren) {
            foreach ($productChildren as $productChildId) {
                if (isset($productAttributes[$productChildId])) {
                    $productChildAttr = $productAttributes[$productChildId];
                    if (!isset($productChildAttr[$this->status->getId()])
                        || !in_array($productChildAttr[$this->status->getId()], $this->statusIds)
                    ) {
                        continue;
                    }

                    $hasChildren = true;
                    $productIndex[$productChildId] = $productChildAttr;
                }
            }
        }
        if ($productChildren !== null && !$hasChildren) {
            $this->next();
        }

        $index = $this->dataProvider->prepareProductIndex($productIndex, $productData, $this->storeId);

        $this->current = $index;
        $this->key = $productData['entity_id'];
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->lastProductId = 0;
        $this->key = null;
        $this->current = null;
        unset($this->products);
        $this->products = [];
        $this->next();
    }
}