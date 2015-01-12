<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Block\Adminhtml\User\Tab\Role;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 * Role Grid on UserEdit page.
 */
class Grid extends GridInterface
{
    /**
     * Grid filters' selectors
     *
     * @var array
     */
    protected $filters = [
        'rolename' => [
            'selector' => '#permissionsUserRolesGrid_filter_role_name',
        ],
    ];

    /**
     * Locator value for role name column
     *
     * @var string
     */
    protected $selectItem = '.col-assigned_user_role > input';
}
