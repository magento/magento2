<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Block\Adminhtml\User\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Mtf\Client\Element;

/**
 * Class Roles
 * Grid on Roles Tab page for User
 */
class Roles extends Grid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '.col-role_name';

    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr';

    /**
     * Filters Name for Roles Grid
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => '#permissionsUserRolesGrid_filter_assigned_user_role',
            'input' => 'select',
        ],
        'role_name' => [
            'selector' => '#permissionsUserRolesGrid_filter_role_name',
        ],
    ];
}
