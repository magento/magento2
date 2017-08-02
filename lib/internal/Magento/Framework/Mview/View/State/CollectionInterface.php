<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View\State;

/**
 * Interface \Magento\Framework\Mview\View\State\CollectionInterface
 *
 * @since 2.0.0
 */
interface CollectionInterface
{
    /**
     * Retrieve loaded states
     *
     * @return array
     * @since 2.0.0
     */
    public function getItems();
}
