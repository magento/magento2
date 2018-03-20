<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Layer;

use Magento\Catalog\Model\Layer\Category\FilterableAttributeList as CategoryFilterableAttributeList;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Layer\Search\FilterableAttributeList;

/**
 * Class FilterableAttributesListFactory
 * @package Magento\CatalogGraphQl\Model\Resolver\Layer
 */
class FilterableAttributesListFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param $type
     * @param array $data
     * @return \Magento\Catalog\Model\Layer\FilterList
     */
    public function create($type, array $data = array())
    {
        if ($type === Resolver::CATALOG_LAYER_CATEGORY) {
            return $this->_objectManager->create(CategoryFilterableAttributeList::class, $data);
        } elseif ($type === Resolver::CATALOG_LAYER_SEARCH) {
            return $this->_objectManager->create(FilterableAttributeList::class, $data);
        }
        throw new \InvalidArgumentException('Unknown filterable attribtues list type: ' . $type);
    }
}
