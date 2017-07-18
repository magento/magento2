<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Locator;

class AttributesGrid extends DataGrid
{
    /**
     * @var string
     */
    protected $selectItem = '[data-action=select-row]';

    /**
     * @var array
     */
    protected $filters = [
        'frontend_label' => [
            'selector' => '[name="frontend_label"]',
        ],
    ];

    /**
     * Mass action toggle button (located in the Grid).
     *
     * @var string
     */
    protected $massActionToggleButton = '//th//button[@data-toggle="dropdown"]';

    /**
     * Mass action toggle list.
     *
     * @var string
     */
    protected $massActionToggleList = './/span[contains(@class, "action-menu-item") and .= "%s"]';

    /**
     * Clear attributes selection
     */
    public function deselectAttributes()
    {
        $actionType = 'Deselect All';
        $this->selectMassAction($actionType);
    }

    /**
     * Do mass select/deselect using the dropdown in the grid.
     *
     * @param string $massActionSelection
     * @return void
     */
    protected function selectMassAction($massActionSelection)
    {
        //Checks which dropdown is visible and uses it.
        for ($i = 1; $i <= 2; $i++) {
            $massActionButton = '(' . $this->massActionToggleButton . ")[$i]";
            $massActionList = '(' . $this->massActionToggleList . ")[$i]";
            if ($this->_rootElement->find($massActionButton, Locator::SELECTOR_XPATH)->isVisible()) {
                $this->_rootElement->find($massActionButton, Locator::SELECTOR_XPATH)->click();
                $this->_rootElement
                    ->find(sprintf($massActionList, $massActionSelection), Locator::SELECTOR_XPATH)
                    ->click();
                break;
            }
        }
    }
}
