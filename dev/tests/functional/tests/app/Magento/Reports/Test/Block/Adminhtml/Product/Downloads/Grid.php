<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Block\Adminhtml\Product\Downloads;

/**
 * Class Grid
 * Downloads Report grid
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name="name"]',
        ],
        'link_title' => [
            'selector' => 'input[name="link_title"]',
        ],
        'sku' => [
            'selector' => 'input[name="sku"]',
        ],
    ];
}
