<?php
/**
 * Serialized definition reader
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Definition\Compiled;

class Serialized extends \Magento\Framework\ObjectManager\Definition\Compiled
{
    /**
     * Mode name
     */
    const MODE_NAME  = 'serialized';

    /**
     * Unpack signature
     *
     * @param string $signature
     * @return mixed
     */
    protected function _unpack($signature)
    {
        return unserialize($signature);
    }
}
