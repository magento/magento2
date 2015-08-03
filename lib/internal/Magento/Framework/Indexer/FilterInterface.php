<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

interface FilterInterface
{
    /**
     * @return void
     */
    public function apply();
}
