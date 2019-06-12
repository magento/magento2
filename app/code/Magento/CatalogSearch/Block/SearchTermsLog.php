<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
<<<<<<< HEAD
 * Class for logging search terms on cached pages
=======
 * Provider of the information on whether the page is cacheable, so that AJAX-based logging of terms can be triggered
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
