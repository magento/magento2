<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;

/**
 * URL Rewrite grid.
 */
class Grid extends ParentGrid
{
    /**
     * Filters array mapping.
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
        'store_id' => [
            'selector' => 'select[name="store_id"]',
            'input' => 'select',
        ],
        'redirect_type' => [
            'selector' => 'select[name="redirect_type"]',
            'input' => 'select',
        ],
    ];
}
