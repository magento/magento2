<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
