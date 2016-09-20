<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Always return empty tags array
 */
class Dummy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTags($object)
    {
        return [];
    }
}
