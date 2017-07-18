<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\CatalogProductLink;

/**
 * Chooser product grid.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr td.col-sku.col-chooser_sku';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name="chooser_name"]',
        ],
        'sku' => [
            'selector' => 'input[name="chooser_sku"]',
        ],
    ];
}
