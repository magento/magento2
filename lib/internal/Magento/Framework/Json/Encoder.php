<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $this->translateInline->processResponseBody($data);
        return \Zend\Json\Encoder::encode($data);
    }
}
