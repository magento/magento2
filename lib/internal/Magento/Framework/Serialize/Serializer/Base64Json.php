<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Serialize\Serializer;

/**
 * Class for serializing data first to json string and then to base64 string.
 *
 * May be used for cases when json encoding results with a string,
 * which contains characters, which are unacceptable by client.
 */
class Base64Json implements \Magento\Framework\Serialize\SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize($data)
    {
        return base64_encode(json_encode($data));
    }

    /**
     * Unserialize the given string with base64 and json.
     * Falls back to the json-only decoding on failure.
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     */
    public function unserialize($string)
    {
        $result = json_decode(base64_decode($string), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_decode($string, true);
        }

        return $result;
    }
}
