<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param int $decodeType
     * @return mixed
     */
    public function decode($data, $decodeType = \Zend\Json\Json::TYPE_ARRAY)
    {
        return \Zend\Json\Decoder::decode($data, $decodeType);
    }
}
