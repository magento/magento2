<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;

/**
 * Class Grid
 * URL Rewrite grid
 */
class Grid extends ParentGrid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'request_path' => [
            'selector' => '#urlrewriteGrid_filter_request_path',
        ],
        'target_path' => [
            'selector' => 'input[name="target_path"]',
        ],
    ];
}
