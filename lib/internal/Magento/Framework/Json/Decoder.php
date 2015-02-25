<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 */
class Decoder implements DecoderInterface
{
    /**
     * Decodes the given $data string which is encoded in the JSON format.
     *
     * @param string $data
     * @param int $objectDecodeType Optional; flag indicating how to decode
     * @return mixed
     */
    public function decode($data, $objectDecodeType = \Zend_Json::TYPE_ARRAY)
    {
        return \Zend_Json::decode($data);
    }
}
