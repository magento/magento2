<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
