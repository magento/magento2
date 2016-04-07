<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Backend catalog price rule grid.
 */
class Catalog extends Grid
{
    /**
     * An element locator which allows to select first entity in grid.
     *
     * @var string
     */
    protected $editLink = '#promo_catalog_grid_table tbody tr:first-child td';

    /**
     * First row selector.
     *
     * @var string
     */
    protected $firstRowSelector = '//tr[@data-role="row"]/td[@data-column="rule_id"]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'rule_id' => [
            'selector' => '#promo_catalog_grid_filter_rule_id',
        ],
        'name' => [
            'selector' => '#promo_catalog_grid_filter_name',
        ],
        'from_date' => [
            'selector' => '[data-ui-id="widget-grid-column-filter-date-filter-from-date-from"]',
        ],
        'to_date' => [
            'selector' => '[data-ui-id="widget-grid-column-filter-date-1-filter-to-date-from"]',
        ],
        'is_active' => [
            'selector' => '#promo_catalog_grid_filter_is_active',
            'input' => 'select',
        ],
        'rule_website' => [
            'selector' => '#promo_catalog_grid_filter_rule_website',
            'input' => 'select',
        ],
    ];

    /**
     * Return row with given catalog price rule name.
     *
     * @param string $ruleName
     * @return SimpleElement
     */
    public function getGridRow($ruleName)
    {
        return $this->getRow(['name' => $ruleName]);
    }

    /**
     * Return id of catalog price rule with given name.
     *
     * @param string $ruleName
     * @return string
     */
    public function getCatalogPriceId($ruleName)
    {
        return $this->getGridRow($ruleName)->find('//td[@data-column="rule_id"]', Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Check if specific row exists in grid.
     *
     * @param array $filter
     * @param bool $isSearchable
     * @param bool $isStrict
     * @return bool
     */
    public function isRowVisible(array $filter, $isSearchable = true, $isStrict = true)
    {
        $this->search(['name' => $filter['name']]);
        return parent::isRowVisible($filter, $isSearchable, $isStrict);
    }
}
