<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Layer;

/**
 * Constructor modification point for Magento\Catalog\Model\Layer.
 *
 * All such context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with their
 * corresponding abstract classes.
 */
interface ContextInterface
{
    /**
     * @return ItemCollectionProviderInterface
     */
    public function getCollectionProvider();

    /**
     * @return StateKeyInterface
     */
    public function getStateKey();

    /**
     * @return CollectionFilterInterface
     */
    public function getCollectionFilter();
}
