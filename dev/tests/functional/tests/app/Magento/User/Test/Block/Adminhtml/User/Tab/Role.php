<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            \Magento\User\Test\Block\Adminhtml\User\Tab\Role\Grid::class,
            ['element' => $this->_rootElement->find('#permissionsUserRolesGrid')]
        );
    }
}
