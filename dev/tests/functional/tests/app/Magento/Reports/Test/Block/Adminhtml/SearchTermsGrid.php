<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml;

/**
 * Class SearchTermsGrid
 * Search Terms report Grid
 */
class SearchTermsGrid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'query_text' => [
            'selector' => 'input[name="query_text"]',
        ],
        'num_results' => [
            'selector' => 'input[name="num_results[from]"]',
        ],
        'popularity' => [
            'selector' => 'input[name="popularity[from]"]',
        ],
    ];

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-query]';
}
