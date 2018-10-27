<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize;

/**
 * This class was introduced only for usage in the \Magento\Framework\DataObject::toJson method.
 * It should not be used in other cases and instead \Magento\Framework\Serialize\Serializer\Json::serialize
 * should be used.
 */
class JsonConverter
{
    /**
     * This method should only be used by \Magento\Framework\DataObject::toJson
     * All other cases should use \Magento\Framework\Serialize\Serializer\Json::serialize directly
     *
     * @param string|int|float|bool|array|null $data
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public static function convert($data)
    {
        $serializer = new \Magento\Framework\Serialize\Serializer\Json();
        return $serializer->serialize($data);
    }
}
