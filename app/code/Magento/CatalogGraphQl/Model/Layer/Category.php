<?php

namespace Magento\CatalogGraphQl\Model\Layer;

class Category extends \Magento\Catalog\Model\Layer
{
    const LAYER_GRAPHQL_CATEGORY = 'graphql_category';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollections;

    public function setCollection(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
    }

    public function getProductCollection()
    {
        return $this->_productCollections[$this->getCurrentCategory()->getId()];
    }
}