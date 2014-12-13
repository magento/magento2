<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Block\Adminhtml\User\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\User\Test\Block\Adminhtml\User\Edit\Tab\Roles;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Form
 * Form for User Edit/Create page
 */
class Form extends FormTabs
{
    /**
     * Role tab id
     *
     * @var string
     */
    protected $roleTab = 'page_tabs_roles_section';

    /**
     * Open Role tab for User Edit page
     *
     * @return void
     */
    public function openRoleTab()
    {
        $this->_rootElement->find($this->roleTab, Locator::SELECTOR_ID)->click();
    }

    /**
     * Get roles grid on user edit page
     *
     * @return Roles
     */
    public function getRolesGrid()
    {
        return $this->blockFactory->create(
            'Magento\User\Test\Block\Adminhtml\User\Edit\Tab\Roles',
            ['element' => $this->_rootElement->find('#permissionsUserRolesGrid')]
        );
    }
}
