<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

class Encoder implements EncoderInterface
{
    /**
     * Translator
     *
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
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
     */
    public function encode($data)
    {
        $json = \Zend_Json::encode($data);
        $this->translateInline->processResponseBody($json, true);
        return $json;
    }
}
