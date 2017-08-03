<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Retrieve list of products where each product contains lower price than others at least for one possible price type
 * @since 2.1.3
 */
class LowestPriceOptionsProvider implements LowestPriceOptionsProviderInterface
{
    /**
     * @var ResourceConnection
     * @since 2.1.3
     */
    private $resource;

    /**
     * @var LinkedProductSelectBuilderInterface
     * @since 2.1.3
     */
    private $linkedProductSelectBuilder;

    /**
     * @var CollectionFactory
     * @since 2.1.3
     */
    private $collectionFactory;

    /**
     * Key is product id. Value is array of prepared linked products
     *
     * @var array
     * @since 2.2.0
     */
    private $linkedProductMap;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     * @since 2.1.3
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
     * @since 2.1.3
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->linkedProductMap[$product->getId()])) {
            $productIds = $this->resource->getConnection()->fetchCol(
                '(' . implode(') UNION (', $this->linkedProductSelectBuilder->build($product->getId())) . ')'
            );

            $this->linkedProductMap[$product->getId()] = $this->collectionFactory->create()
                ->addAttributeToSelect(['price', 'special_price', 'special_from_date', 'special_to_date'])
                ->addIdFilter($productIds)
                ->getItems();
        }
        return $this->linkedProductMap[$product->getId()];
    }
}
