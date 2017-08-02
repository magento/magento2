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
 * @since 2.0.0
 */
interface EncoderInterface
{
    /**
     * base64_encode() for URLs encoding
     *
     * @param    string $url
     * @return   string
     * @since 2.0.0
     */
    public function encode($url);
}
