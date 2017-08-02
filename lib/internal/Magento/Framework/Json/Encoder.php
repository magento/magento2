<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 * @deprecated 2.2.0 @see \Magento\Framework\Serialize\Serializer\Json::serialize
 * @since 2.0.0
 */
class Encoder implements EncoderInterface
{
    /**
     * Translator
     *
     * @var \Magento\Framework\Translate\InlineInterface
     * @since 2.0.0
     */
    protected $translateInline;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Translate\InlineInterface $translateInline)
    {
        $this->translateInline = $translateInline;
    }

    /**
     * Encode the mixed $data into the JSON format.
     *
     * @param mixed $data
     * @return string
     * @since 2.0.0
     */
    public function encode($data)
    {
        $this->translateInline->processResponseBody($data);
        return \Zend_Json::encode($data);
    }
}
