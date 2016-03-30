<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Search Engine interface
 */
interface SearchEngineInterface
{
    /**
     * Process Search Request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function search(RequestInterface $request);
}
