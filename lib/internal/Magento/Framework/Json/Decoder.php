<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * phpcs:ignore Magento2.Commenting.ClassAndInterfacePHPDocFormatting
 * @deprecated 101.0.0 @see \Magento\Framework\Serialize\Serializer\Json::unserialize
 */
class Decoder implements DecoderInterface
{
    /**
     * @var JsonSerializer
     */
    private JsonSerializer $jsonSerializer;

    /**
     * @param JsonSerializer $serializer
     */
    public function __construct(JsonSerializer $serializer)
    {
        $this->jsonSerializer = $serializer;
    }

    /**
     * Decodes the given $data string which is encoded in the JSON format.
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data)
    {
        return $this->jsonSerializer->unserialize((string) $data);
    }
}
