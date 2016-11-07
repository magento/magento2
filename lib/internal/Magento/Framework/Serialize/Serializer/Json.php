<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class for serializing data to json string and unserializing json string to data
 */
class Json implements SerializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($string)
    {
        return json_decode($string, true);
    }
}
