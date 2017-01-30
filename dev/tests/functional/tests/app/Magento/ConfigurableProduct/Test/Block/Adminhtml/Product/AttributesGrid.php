<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

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
}
