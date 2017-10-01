<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Import\Serializer;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * @inheritdoc
 */
class Json implements SerializerInterface, LegacyJsonHelperInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Json constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @return string
     */
    public function jsonEncode($valueToEncode)
    {
        return $this->serializer->serialize($valueToEncode);
    }

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * @param string $encodedValue
     * @return mixed
     */
    public function jsonDecode($encodedValue)
    {
        return $this->serializer->unserialize($encodedValue);
    }

    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @return string|bool
     * @throws \InvalidArgumentException
     * @since 100.2.0
     */
    public function serialize($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     * @since 100.2.0
     */
    public function unserialize($string)
    {
        return $this->serializer->unserialize($string);
    }
}
