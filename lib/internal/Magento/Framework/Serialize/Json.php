<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize;

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
