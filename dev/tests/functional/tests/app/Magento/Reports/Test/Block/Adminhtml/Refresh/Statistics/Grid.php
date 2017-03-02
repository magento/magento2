<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Refresh\Statistics;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;
use Magento\Mtf\Client\Locator;

/**
 * Refresh statistics grid.
 */
class Grid extends AbstractGrid
{
    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = '//tr[td[contains(@class,"col-report") and normalize-space(.)="%s"]]//input';

    /**
     * Search for item and select it.
     *
     * @param array $filter
     * @throws \Exception
     * @return void
     */
    public function searchAndSelect(array $filter)
    {
        $selectItem = $this->_rootElement->find(sprintf($this->selectItem, $filter['report']), Locator::SELECTOR_XPATH);
        if ($selectItem->isVisible()) {
            $selectItem->click();
        } else {
            throw new \Exception("Searched item was not found by filter\n" . print_r($filter, true));
        }
    }

    /**
     * Get rows data.
     *
     * @param array $columns
     * @return array
     */
    public function getRowsData(array $columns)
    {
        $data = [];
        $rows = $this->_rootElement->getElements($this->rowItem);
        foreach ($rows as $row) {
            $rowData = [];
            foreach ($columns as $columnName) {
                $rowData[$columnName] = trim($row->find('.col-' . $columnName)->getText());
            }

            $data[] = $rowData;
        }

        return $data;
    }
}
