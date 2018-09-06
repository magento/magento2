<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class for logging search terms on cached pages
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class SearchTermsLog implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(
        ResponseInterface $response
    ) {
        $this->response = $response;
    }

    /**
     * Check is current page cacheable
     *
     * @return bool
     */
    public function isPageCacheable()
    {
        $pragma = $this->response->getHeader('pragma')->getFieldValue();
        return ($pragma == 'cache');
    }
}
