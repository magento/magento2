<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Test\Block\Adminhtml\Rate;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 * Adminhtml Tax Rates management grid
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
            'selector' => '#tax_rate_grid_filter_code',
        ],
        'tax_country_id' => [
            'selector' => '#tax_rate_grid_filter_tax_country_id',
            'input' => 'select',
        ],
        'tax_postcode' => [
            'selector' => '#tax_rate_grid_filter_tax_postcode',
        ],
    ];
}
