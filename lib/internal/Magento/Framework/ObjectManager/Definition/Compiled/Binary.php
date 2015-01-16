<?php
/**
 * Igbinary serialized definition reader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Definition\Compiled;

class Binary extends \Magento\Framework\ObjectManager\Definition\Compiled
{
    /**
     * Unpack signature
     *
     * @param string $signature
     * @return mixed
     */
    protected function _unpack($signature)
    {
        return igbinary_unserialize($signature);
    }
}
