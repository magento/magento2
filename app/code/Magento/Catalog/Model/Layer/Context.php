<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer;

/**
 * Constructor modification point for Magento\Catalog\Model\Layer.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 * @since 2.0.0
 */
class Context implements ContextInterface
{
    /**
     * @var ItemCollectionProviderInterface
     * @since 2.0.0
     */
    protected $collectionProvider;

    /**
     * @var StateKeyInterface
     * @since 2.0.0
     */
    protected $stateKey;

    /**
     * @var CollectionFilterInterface
     * @since 2.0.0
     */
    protected $collectionFilter;

    /**
     * @param ItemCollectionProviderInterface $collectionProvider
     * @param StateKeyInterface $stateKey
     * @param CollectionFilterInterface $collectionFilter
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCollectionProvider()
    {
        return $this->collectionProvider;
    }

    /**
     * @return StateKeyInterface
     * @since 2.0.0
     */
    public function getStateKey()
    {
        return $this->stateKey;
    }

    /**
     * @return CollectionFilterInterface
     * @since 2.0.0
     */
    public function getCollectionFilter()
    {
        return $this->collectionFilter;
    }
}
