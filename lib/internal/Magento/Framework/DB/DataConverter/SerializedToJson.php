<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\DataConverter;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Convert from serialized to JSON format
 */
class SerializedToJson implements DataConverterInterface
{
    /**
     * @var Serialize
     */
    private $serialize;

    /**
     * @var Json
     */
    private $json;

    /**
     * Constructor
     *
     * @param Serialize $serialize
     * @param Json $json
     */
    public function __construct(
        Serialize $serialize,
        Json $json
    ) {
        $this->serialize = $serialize;
        $this->json = $json;
    }

    /**
     * Convert from serialized to JSON format
     *
     * @param string $value
     * @return string
     * @throws DataConversionException
     */
    public function convert($value)
    {
        return $this->encodeJson($this->unserializeValue($value));
    }

    /**
     * Unserialize value
     *
     * @param string $value
     * @return  mixed
     * @throws DataConversionException
     */
    protected function unserializeValue($value)
    {
        try {
            if (PHP_MAJOR_VERSION >= 7) {
                $value = $this->serialize->unserialize($value);
            } else {
                set_error_handler(function ($errorNumber, $errorString) {
                    throw new DataConversionException($errorString);
                });
                $value = $this->serialize->unserialize($value);
                restore_error_handler();
            }
        } catch (\Throwable $throwable) {
            throw new DataConversionException($throwable->getMessage());
        }
        return $value;
    }

    /**
     * Ecode value with json encoder
     *
     * @param string $value
     * @return bool|string
     * @throws DataConversionException
     */
    protected function encodeJson($value)
    {
        $value = $this->json->serialize($value);
        if (json_last_error()) {
            throw new DataConversionException(json_last_error_msg());
        }
        return $value;
    }
}
