<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

use Magento\Framework\UrlInterface;

/**
 * Class \Magento\Framework\Url\Decoder
 *
 * @since 2.0.0
 */
class Decoder implements DecoderInterface
{
    /**
     * @var UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @since 2.0.0
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * base64_decode() for URLs decoding
     *
     * @param    string $url
     * @return   string
     * @since 2.0.0
     */
    public function decode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        return $this->urlBuilder->sessionUrlVar($url);
    }
}
