<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Definition\Serializer;

class Standard implements SerializerInterface
{
    /**
     * Serializer name
     */
    const NAME  = 'standard';

    /**
     * Serialize input data
     *
     * @param mixed $data
     * @return string
     */
    public function serialize($data)
    {
        return serialize($data);
    }
}
