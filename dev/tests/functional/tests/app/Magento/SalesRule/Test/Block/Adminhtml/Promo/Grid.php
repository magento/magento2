<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo;

use Magento\Mtf\Client\Locator;

/**
 * Backend sales rule grid.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Id of a row selector.
     *
     * @var string
     */
    protected $rowIdSelector = 'td.col-rule_id';

    /**
     * Locator for promo quote form.
     *
     * @var string
     */
    protected $promoQuoteFormSelector = 'div#promo_catalog_edit_tabs';

    /**
     * First row selector
     *
     * @var string
     */
    protected $firstRowSelector = '//tr[1]/td[@data-column="name"]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'rule_id' => [
            'selector' => '#promo_quote_grid_filter_rule_id',
        ],
        'name' => [
            'selector' => 'input[name="name"]',
        ],
    ];

    /**
     * Locator value for link in sales rule name column.
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-name]';

    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-name';

    /**
     * Return the id of the row that matched the search filter.
     *
     * @param $filter
     * @param bool $isSearchable
     * @return array|int|string
     */
    public function getIdOfRow($filter, $isSearchable = true)
    {
        $rid = -1;
        $this->search($filter, $isSearchable);
        $rowItem = $this->_rootElement->find($this->rowItem, Locator::SELECTOR_CSS);
        if ($rowItem->isVisible()) {
            $idElement = $rowItem->find($this->rowIdSelector);
            $rid = $idElement->getText();
        }
        return $rid;
    }
}
