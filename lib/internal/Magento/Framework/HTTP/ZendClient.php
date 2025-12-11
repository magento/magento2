<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Magento HTTP Client
 */
namespace Magento\Framework\HTTP;

/**
 * @deprecated The class is deprecated due to migration from Zend_Http to laminas-http.
 * @see Use \Magento\Framework\HTTP\LaminasClient insted.
 */
class ZendClient
{
    /**
     * @param null|string $uri
     * @param null|array $config
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct($uri = null, $config = null)
    {
        trigger_error('Class is deprecated', E_USER_DEPRECATED);
    }

    /**
     * Perform an HTTP request
     *
     * @param null|string $method
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function request($method = null)
    {
        trigger_error('Class is deprecated', E_USER_DEPRECATED);
    }

    /**
     * Change value of internal flag to disable/enable custom prepare functionality
     *
     * @param bool $flag
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setUrlEncodeBody($flag)
    {
        trigger_error('Class is deprecated', E_USER_DEPRECATED);
    }
}
