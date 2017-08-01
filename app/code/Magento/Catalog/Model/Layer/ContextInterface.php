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
 * All such context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with their
 * corresponding abstract classes.
 * @since 2.0.0
 */
interface ContextInterface
{
    /**
     * @return ItemCollectionProviderInterface
     * @since 2.0.0
     */
    public function getCollectionProvider();

    /**
     * @return StateKeyInterface
     * @since 2.0.0
     */
    public function getStateKey();

    /**
     * @return CollectionFilterInterface
     * @since 2.0.0
     */
    public function getCollectionFilter();
}
