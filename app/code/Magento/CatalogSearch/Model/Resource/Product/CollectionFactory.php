<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource\Product;

class CollectionFactory
{
    const PRODUCT_COLLECTION_FULLTEXT = 'catalogSearchFulltextCollection';
    const PRODUCT_COLLECTION_ADVANCED = 'catalogSearchAdvancedCollection';

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Array of product collection factory names
     *
     * @var array
     */
    protected $productFactoryNames;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $productFactoryNames
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $productFactoryNames
    ) {
        $this->objectManager = $objectManager;
        $this->productFactoryNames = $productFactoryNames;
    }

    /**
     * Create collection instance with specified parameters
     *
     * @param string $collectionName
     * @param array $data
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function create($collectionName, array $data = [])
    {
        if (!isset($this->productFactoryNames[$collectionName])) {
            throw new \RuntimeException(sprintf('Collection "%s" has not been set', $collectionName));
        }
        $instance = $this->objectManager->create($this->productFactoryNames[$collectionName], $data);
        if (!$instance instanceof \Magento\Catalog\Model\Resource\Product\Collection) {
            throw new \RuntimeException(
                $this->productFactoryNames[$collectionName] .
                ' is not instance of \Magento\Catalog\Model\Resource\Product\Collection'
            );
        }
        return $instance;
    }
}
