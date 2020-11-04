<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * @deprecated 101.0.0 @see \Magento\Framework\Serialize\Serializer\Json::serialize
 */
class Encoder implements EncoderInterface
{
    /**
     * Encode the mixed $data into the JSON format.
     *
     * @param mixed $data
     * @return string
     */
    public function encode($data)
    {
        return \Zend_Json::encode($data);
    }
}
