<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Test\Page\Adminhtml;

use Mtf\Page\BackendPage;

/**
 * Class UserEdit
 */
class UserEdit extends BackendPage
{
    const MCA = 'admin/user/edit';

    protected $_blocks = [
        'pageActions' => [
            'name' => 'pageActions',
            'class' => 'Magento\Backend\Test\Block\FormPageActions',
            'locator' => '.page-main-actions',
            'strategy' => 'css selector',
        ],
        'messagesBlock' => [
            'name' => 'messagesBlock',
            'class' => 'Magento\Core\Test\Block\Messages',
            'locator' => '#messages',
            'strategy' => 'css selector',
        ],
        'userForm' => [
            'name' => 'userForm',
            'class' => 'Magento\User\Test\Block\Adminhtml\User\UserForm',
            'locator' => '[id="page:main-container"]',
            'strategy' => 'css selector',
        ],
        'rolesGrid' => [
            'name' => 'rolesGrid',
            'class' => 'Magento\User\Test\Block\Adminhtml\User\Tab\Role\Grid',
            'locator' => '[id="permissionsUserRolesGrid"]',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * @return \Magento\Backend\Test\Block\FormPageActions
     */
    public function getPageActions()
    {
        return $this->getBlockInstance('pageActions');
    }

    /**
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return $this->getBlockInstance('messagesBlock');
    }

    /**
     * @return \Magento\User\Test\Block\Adminhtml\User\UserForm
     */
    public function getUserForm()
    {
        return $this->getBlockInstance('userForm');
    }

    /**
     * @return \Magento\User\Test\Block\Adminhtml\User\Tab\Role\Grid
     */
    public function getRolesGrid()
    {
        return $this->getBlockInstance('rolesGrid');
    }
}
