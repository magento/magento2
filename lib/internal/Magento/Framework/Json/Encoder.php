<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Translate\InlineInterface;

/**
 * phpcs:ignore Magento2.Commenting.ClassAndInterfacePHPDocFormatting
 * @deprecated 101.0.0 @see \Magento\Framework\Serialize\Serializer\Json::serialize
 */
class Encoder implements EncoderInterface
{
    /**
     * Translator
     *
     * @var InlineInterface
     */
    protected InlineInterface $translateInline;

    /**
     * @var JsonSerializer
     */
    private JsonSerializer $jsonSerializer;

    /**
     * @param InlineInterface $translateInline
     * @param JsonSerializer $serializer
     */
    public function __construct(InlineInterface $translateInline, JsonSerializer $serializer)
    {
        $this->translateInline = $translateInline;
        $this->jsonSerializer = $serializer;
    }

    /**
     * Encode the mixed $data into the JSON format.
     *
     * @param mixed $valueToEncode
     * @return string
     */
    public function encode($valueToEncode)
    {
        if (is_object($valueToEncode)) {
            if (method_exists($valueToEncode, 'toJson')) {
                return $valueToEncode->toJson();
            }

            if (method_exists($valueToEncode, 'toArray')) {
                return self::encode($valueToEncode->toArray());
            }
        }
        $this->translateInline->processResponseBody($valueToEncode);

        return $this->jsonSerializer->serialize($valueToEncode);
    }
}
