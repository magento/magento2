<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize;

/**
 * Used to convert \Magento\Framework\DataObject to Json
 *
 * @deprecated @see \Magento\Framework\Serialize\Serializer\Json::serialize
 */
class JsonConverter
{
    /**
     * @param $data
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public static function convert($data)
    {
        $serializer = new \Magento\Framework\Serialize\Serializer\Json();
        return $serializer->serialize($data);
    }
}
