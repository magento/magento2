<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Retrieve list of products where each product contains lower price than others at least for one possible price type
 */
class LowestPriceOptionsProvider implements LowestPriceOptionsProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var LinkedProductSelectBuilderInterface
     */
    private $linkedProductSelectBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Key is product id. Value is prepared product collection
     *
     * @var array
     */
    private $productsMap;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resourceConnection;
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->productsMap[$product->getId()])) {
            $productIds = $this->resource->getConnection()->fetchCol(
                '(' . implode(') UNION (', $this->linkedProductSelectBuilder->build($product->getId())) . ')'
            );

            $this->productsMap[$product->getId()] = $this->collectionFactory->create()
                ->addAttributeToSelect(['price', 'special_price'])
                ->addIdFilter($productIds)
                ->getItems();
        }
        return $this->productsMap[$product->getId()];
    }
}
