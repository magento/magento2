<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\DataConverter;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;

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
        if ($this->isValidJsonValue($value)) {
            return $value;
        }
        return $this->encodeJson($this->unserializeValue($value));
    }

    /**
     * Is a valid JSON serialized value
     *
     * @param string $value
     * @return bool
     */
    protected function isValidJsonValue($value)
    {
        if (in_array($value, ['null', 'false', '0', '""', '[]'])
            || (json_decode($value) !== null && json_last_error() === JSON_ERROR_NONE)
        ) {
            return true;
        }
        //JSON last error reset
        json_encode([]);
        return false;
    }

    /**
     * Unserialize value
     *
     * @param string $value
     * @return mixed
     * @throws DataConversionException
     */
    protected function unserializeValue($value)
    {
        try {
            set_error_handler(function ($errorNumber, $errorString) {
                throw new DataConversionException($errorString, $errorNumber);
            });
            $value = $this->serialize->unserialize($value);
        } catch (\Throwable $throwable) {
            throw new DataConversionException($throwable->getMessage());
        } finally {
            restore_error_handler();
        }
        return $value;
    }

    /**
     * Encode value with json encoder
     *
     * @param string $value
     * @return string
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
