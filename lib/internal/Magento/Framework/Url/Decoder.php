<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

use Magento\Framework\UrlInterface;

class Decoder implements DecoderInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * The base64_decode() for URLs decoding
     *
     * @param    string $url
     * @return   string
     */
    public function decode($url)
    {
        $url = $url !== null ? base64_decode(strtr($url, '-_,', '+/=')) : '';
        return $this->urlBuilder->sessionUrlVar($url);
    }
}
