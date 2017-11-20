<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Framework\App\ObjectManager;
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
     * @var LinkedProductSelectBuilderInterface
     */
    private $linkedProductSelectBuilder;

    /**
     * @var OptionsCollectionProvider
     */
    private $optionsCollectionProvider;

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
     * @param OptionsCollectionProvider|null $optionsCollectionProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder,
        CollectionFactory $collectionFactory,
        OptionsCollectionProvider $optionsCollectionProvider = null
    ) {
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;

        $this->optionsCollectionProvider = $optionsCollectionProvider ?:
            ObjectManager::getInstance()->get(OptionsCollectionProvider::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->productsMap[$product->getId()])) {
            $optionsCollection = $this->optionsCollectionProvider->getCollection(
                $this->linkedProductSelectBuilder->build($product->getId())
            );

            $this->productsMap[$product->getId()] = $optionsCollection->getItems();
        }

        return $this->productsMap[$product->getId()];
    }
}
