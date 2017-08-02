<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Encryption;

/**
 * @api
 * @since 2.0.0
 */
class UrlCoder
{
    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_url;

    /**
     * @param \Magento\Framework\UrlInterface $url
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\UrlInterface $url)
    {
        $this->_url = $url;
    }

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

    /**
     *  base64_decode() for URLs decoding
     *
     * @param    string $url
     * @return   string
     * @since 2.0.0
     */
    public function decode($url)
    {
        return $this->_url->sessionUrlVar(base64_decode(strtr($url, '-_,', '+/=')));
    }
}
