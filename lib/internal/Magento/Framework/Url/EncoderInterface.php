<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Url;

/**
 * Base64 encoder for URLs
 *
 * @api
 * @since 100.0.2
 */
interface EncoderInterface
{
    /**
     * base64_encode() for URLs encoding
     *
     * @param    string $url
     * @return   string
     */
    public function encode($url);
}
