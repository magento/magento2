<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\CmsPageLink;

/**
 * Chooser page grid.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr td.col-title.col-chooser_title';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'title' => [
            'selector' => 'input[name="chooser_title"]',
        ],
    ];
}
