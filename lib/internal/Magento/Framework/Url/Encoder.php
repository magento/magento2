<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Class \Magento\Framework\Url\Encoder
 *
 * @since 2.0.0
 */
class Encoder implements EncoderInterface
{
    /**
     * base64_encode() for URLs encoding
     *
     * @param    string $url
     * @return   string
     * @since 2.0.0
     */
    public function encode($url)
    {
        return strtr(base64_encode($url), '+/=', '-_,');
    }
}
