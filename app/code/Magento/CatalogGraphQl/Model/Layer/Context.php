<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\Model\Layer;

use Magento\Catalog\Model\Layer\CollectionFilterInterface;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Layer\StateKeyInterface;

/**
 * Class Context
 * @package Magento\CatalogGraphQl\Model\Layer
 */
class Context implements \Magento\Catalog\Model\Layer\ContextInterface
{
    /**
     * @var ItemCollectionProviderInterface
     */
    protected $collectionProvider;

    /**
     * @var StateKeyInterface
     */
    protected $stateKey;

    /**
     * @var CollectionFilterInterface
     */
    protected $collectionFilter;

    /**
     * @param ItemCollectionProviderInterface $collectionProvider
     * @param StateKeyInterface $stateKey
     * @param CollectionFilterInterface $collectionFilter
     */
    public function __construct(
        ItemCollectionProviderInterface $collectionProvider,
        StateKeyInterface $stateKey,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->collectionProvider = $collectionProvider;
        $this->stateKey = $stateKey;
        $this->collectionFilter = $collectionFilter;
    }

    /**
     * @return ItemCollectionProviderInterface
     */
    public function getCollectionProvider()
    {
        return $this->collectionProvider;
    }

    /**
     * @return StateKeyInterface
     */
    public function getStateKey()
    {
        return $this->stateKey;
    }

    /**
     * @return CollectionFilterInterface
     */
    public function getCollectionFilter()
    {
        return $this->collectionFilter;
    }
}
