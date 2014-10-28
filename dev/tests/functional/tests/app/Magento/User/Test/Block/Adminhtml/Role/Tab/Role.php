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
