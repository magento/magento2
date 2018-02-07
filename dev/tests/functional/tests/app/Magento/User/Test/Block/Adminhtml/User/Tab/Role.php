<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Block\Adminhtml\User\Tab;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Class Role
 * User role tab on UserEdit page.
 */
class Role extends Tab
{
    /**
     * Fill user options
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return void
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        $this->getRoleGrid()->searchAndSelect(['rolename' => $fields['role_id']['value']]);
    }

    /**
     * Returns role grid
     *
     * @return \Magento\User\Test\Block\Adminhtml\User\Tab\Role\Grid
     */
    public function getRoleGrid()
    {
        return $this->blockFactory->create(
            'Magento\User\Test\Block\Adminhtml\User\Tab\Role\Grid',
            ['element' => $this->_rootElement->find('#permissionsUserRolesGrid')]
        );
    }
}
