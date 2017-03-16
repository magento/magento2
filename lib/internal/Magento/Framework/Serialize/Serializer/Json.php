<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    public function serialize($data, $options = [])
    {
        $encodeOptions = 0;
        foreach ($options as $option) {
            $encodeOptions |= $option;
        }
        return json_encode($data, $encodeOptions);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($string)
    {
        return json_decode($string, true);
    }
}
