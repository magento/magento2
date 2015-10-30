<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

interface SearchResponseBuilderInterface
{
    /**
     * @param \Magento\Framework\Search\ResponseInterface $response
     * @return \Magento\Framework\Api\Search\SearchResultInterface
     */
    public function build(\Magento\Framework\Search\ResponseInterface $response);
}
