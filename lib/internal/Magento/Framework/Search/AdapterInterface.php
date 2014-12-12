<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search;

use Magento\Framework\Search\Response\QueryResponse;

/**
 * Search Adapter interface
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
