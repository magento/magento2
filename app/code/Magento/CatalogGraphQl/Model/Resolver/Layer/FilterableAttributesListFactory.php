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
 * Factory for filterable attributes list.
 *
 * @package Magento\CatalogGraphQl\Model\Resolver\Layer
 */
class FilterableAttributesListFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
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
            return $this->objectManager->create(CategoryFilterableAttributeList::class, $data);
        } elseif ($type === Resolver::CATALOG_LAYER_SEARCH) {
            return $this->objectManager->create(FilterableAttributeList::class, $data);
        }
        throw new \InvalidArgumentException('Unknown filterable attribtues list type: ' . $type);
    }
}
