<?php
/**
 * PATH_INFO processor
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Request;

/**
 * @api
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
