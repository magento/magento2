<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

class Encoder implements EncoderInterface
{
    /**
     * base64_encode() for URLs encoding
     *
     * @param    string $url
     * @return   string
     */
    public function encode($url)
    {
        return strtr(base64_encode($url), '+/=', '-_,');
    }
}
