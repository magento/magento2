<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Url;

/**
 * Base64 decoder for URLs
 *
 * @api
 * @since 100.0.2
 */
interface DecoderInterface
{
    /**
     * base64_decode() for URLs decoding
     *
     * @param    string $url
     * @return   string
     */
    public function decode($url);
}
