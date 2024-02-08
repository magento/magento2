<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search;

/**
 * Search Engine interface
 *
 * @api
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
