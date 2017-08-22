<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * @deprecated 100.2.0 @see \Magento\Framework\Serialize\Serializer\Json::serialize
 */
class Encoder implements EncoderInterface
{
    /**
     * Translator
     *
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->translateInline = $translateInline;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Encode the mixed $data into the JSON format.
     *
     * @param mixed $data
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public function encode($data)
    {
        $this->translateInline->processResponseBody($data);
        return $this->serializer->serialize($data);
    }
}
