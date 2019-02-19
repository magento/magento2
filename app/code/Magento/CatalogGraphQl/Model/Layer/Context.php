<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Layer;

use Magento\Catalog\Model\Layer\CollectionFilterInterface;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Layer\StateKeyInterface;

/**
 * Context for graphql layered navigation.
 */
class Context implements \Magento\Catalog\Model\Layer\ContextInterface
{
    /**
     * @var ItemCollectionProviderInterface
     */
    private $collectionProvider;

    /**
     * @var StateKeyInterface
     */
    private $stateKey;

    /**
     * @var CollectionFilterInterface
     */
    private $collectionFilter;

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
    public function getCollectionProvider() : ItemCollectionProviderInterface
    {
        return $this->collectionProvider;
    }

    /**
     * @return StateKeyInterface
     */
    public function getStateKey() : StateKeyInterface
    {
        return $this->stateKey;
    }

    /**
     * @return CollectionFilterInterface
     */
    public function getCollectionFilter() : CollectionFilterInterface
    {
        return $this->collectionFilter;
    }
}
