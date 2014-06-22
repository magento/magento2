<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * Check if specific row exists in grid
     *
     * @param array $filter
     * @param bool $isSearchable
     * @param bool $isStrict
     * @return bool
     */
    public function isRowVisible(array $filter, $isSearchable = false, $isStrict = true)
    {
        $this->search(array('code' => $filter['code']));
        return parent::isRowVisible($filter, $isSearchable);
    }
}
