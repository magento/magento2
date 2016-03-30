<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid as GridAbstract;

/**
 * Class Grid
 * Reviews grid
 */
class Grid extends GridAbstract
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'review_id' => [
            'selector' => 'input[name="review_id"]',
        ],
        'title' => [
            'selector' => 'input[name="title"]',
        ],
        'status' => [
            'selector' => '#reviwGrid_filter_status',
            'input' => 'select',
        ],
        'nickname' => [
            'selector' => 'input[name="nickname"]',
        ],
        'detail' => [
            'selector' => 'input[name="detail"]',
        ],
        'visible_in' => [
            'selector' => 'select[name="visible_in"]',
            'input' => 'selectstore',
        ],
        'type' => [
            'selector' => 'select[name="type"]',
            'input' => 'select',
        ],
        'name' => [
            'selector' => 'input[name="name"]',
        ],
        'sku' => [
            'selector' => 'input[name="sku"]',
        ],
    ];
}
