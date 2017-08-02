<?php
/**
 * PATH_INFO processor
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Request;

/**
 * @api
 * @since 2.0.0
 */
interface PathInfoProcessorInterface
{
    /**
     * Process Request path info
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     * @since 2.0.0
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo);
}
