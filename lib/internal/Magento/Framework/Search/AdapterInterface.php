<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search;

use Magento\Framework\Search\Response\QueryResponse;

/**
 * Search Adapter interface
 *
 * @api
 */
interface AdapterInterface
{
    /**
     * Process Search Request
     *
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request);
}
