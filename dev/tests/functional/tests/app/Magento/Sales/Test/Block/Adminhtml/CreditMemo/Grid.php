<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Adminhtml\CreditMemo;

/**
 * Class Grid
 * Credit memo grid on Credit memos index page
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="real_creditmemo_id"]',
        ],
        'order_id' => [
            'selector' => 'input[name="order_increment_id"]',
        ],
        'grand_total_from' => [
            'selector' => 'input[name="base_grand_total[from]"]',
        ],
        'grand_total_to' => [
            'selector' => 'input[name="base_grand_total[to]"]',
        ],
    ];
}
