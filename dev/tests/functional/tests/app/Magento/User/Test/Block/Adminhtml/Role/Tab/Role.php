<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Block\Adminhtml\Role\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Client\Element;

/**
 * Class Role
 * Respond for filing data in roles users tab
 */
class Role extends Tab
{
    /**
     * Fills username in user grid
     *
     * @param array $fields
     * @param Element $element
     * @return void
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        $users = (is_array($fields['in_role_users']['value']))
            ? $fields['in_role_users']['value']
            : [$fields['in_role_users']['value']];
        foreach ($users as $user) {
            $this->getUserGrid()->searchAndSelect(['username' => $user]);
        }
    }

    /**
     * Returns user grid block
     *
     * @return \Magento\User\Test\Block\Adminhtml\Role\Tab\User\Grid
     */
    public function getUserGrid()
    {
        return $this->blockFactory->create(
            'Magento\User\Test\Block\Adminhtml\Role\Tab\User\Grid',
            ['element' => $this->_rootElement->find('#roleUserGrid')]
        );
    }
}
