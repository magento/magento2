<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Base64 encoder for URLs
 *
 * @api
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
