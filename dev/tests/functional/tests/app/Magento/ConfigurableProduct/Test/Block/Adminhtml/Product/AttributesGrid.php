<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Clear attributes selection
     */
    public function deselectAttributes()
    {
        $actionType = 'Deselect All';
        $this->selectMassAction($actionType);
    }
}
