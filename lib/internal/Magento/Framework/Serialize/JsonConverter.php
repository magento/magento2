<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize;

/**
<<<<<<< HEAD
 * This class was introduced only for usage in the \Magento\Framework\DataObject::toJson method.
=======
 * This class was introducted only for usage in the \Magento\Framework\DataObject::toJson method.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
