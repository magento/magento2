<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View\State;

/**
 * Interface \Magento\Framework\Mview\View\State\CollectionInterface
 *
 * @api
 */
interface CollectionInterface
{
    /**
     * Retrieve loaded states
     *
     * @return array
     */
    public function getItems();
}
