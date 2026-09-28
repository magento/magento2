<?php
/**
 * PATH_INFO processor
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Request;

/**
 * @api
 * @since 100.0.2
 */
interface PathInfoProcessorInterface
{
    /**
     * Process Request path info
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo);
}
