<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class RoleGrid
 * Role grid on role index page
 *
 * @package Magento\User\Test\Block\Adminhtml
 */
class RoleGrid extends Grid
{
    /**
     * Grid filters' selectors
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => '#roleGrid_filter_role_id',
        ],
        'rolename' => [
            'selector' => '#roleGrid_filter_role_name',
        ],
    ];

    /**
     * Locator value for td with role name
     *
     * @var string
     */
    protected $editLink = 'tbody [data-column="role_name"]';
}
