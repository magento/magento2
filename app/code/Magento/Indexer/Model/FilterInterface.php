<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

interface FilterInterface
{
    /**
     * @return void
     */
    public function apply();
}
