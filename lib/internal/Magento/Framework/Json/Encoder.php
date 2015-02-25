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
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function encode($data, $cycleCheck = false, $options = [])
    {
        $json = \Zend_Json::encode($data, $cycleCheck, $options);
        $this->translateInline->processResponseBody($json, true);
        return $json;
    }
}
