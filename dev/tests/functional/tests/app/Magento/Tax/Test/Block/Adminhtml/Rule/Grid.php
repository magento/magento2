<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Block\Adminhtml\Rule;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 * Adminhtml Tax Rules management grid
 */
class Grid extends GridInterface
{
    /**
     * Locator value for opening needed row
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-code]';

    /**
     * Initialize block elements
     *
     * @var array
     */
    protected $filters = [
        'code' => [
            'selector' => '#taxRuleGrid_filter_code',
        ],
        'tax_customer_class' => [
            'selector' => '#taxRuleGrid_filter_customer_tax_classes',
            'input' => 'select',
        ],
        'tax_product_class' => [
            'selector' => '#taxRuleGrid_filter_product_tax_classes',
            'input' => 'select',
        ],
        'tax_rate' => [
            'selector' => '#taxRuleGrid_filter_tax_rates',
            'input' => 'select',
        ],
    ];

    /**
     * First row selector
     *
     * @var string
     */
    protected $firstRowSelector = '//tbody/tr[./td[contains(@class, "col-name")]][1]';

    /**
     * Check if specific row exists in grid
     *
     * @param array $filter
     * @param bool $isSearchable
     * @param bool $isStrict
     * @return bool
     */
    public function isRowVisible(array $filter, $isSearchable = false, $isStrict = true)
    {
        $this->search(['code' => $filter['code']]);
        return parent::isRowVisible($filter, $isSearchable);
    }
}
