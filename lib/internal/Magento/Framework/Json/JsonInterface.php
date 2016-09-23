<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

interface JsonInterface
{
    const TYPE_ARRAY  = 1;

    const TYPE_OBJECT = 0;

    /**
     * Encode $data into the JSON format
     *
     * @param array|string $data
     * @param int $options
     * @return string|bool
     */
    public function encode($data, $options = 0);

    /**
     * Decode the given string which is encoded in the JSON format
     *
     * @param string $string
     * @param int $objectDecodeType
     * @return array|\stdClass
     */
    public function decode($string, $objectDecodeType = self::TYPE_ARRAY);
}
