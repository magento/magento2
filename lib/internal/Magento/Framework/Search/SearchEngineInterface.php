<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Search Engine interface
 * @since 2.0.0
 */
interface SearchEngineInterface
{
    /**
     * Process Search Request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @since 2.0.0
     */
    public function search(RequestInterface $request);
}
