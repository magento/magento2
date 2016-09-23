<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

class Json implements JsonInterface
{
    /**
     * {@inheritDoc}
     */
    public function encode($data, $options = 0)
    {
        return json_encode($data, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function decode($string, $objectDecodeType = self::TYPE_ARRAY)
    {
        $result = json_decode($string, $objectDecodeType);
        return $result;
    }
}
