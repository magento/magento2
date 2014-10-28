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

namespace Magento\User\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class AdminUserRole
 * Predefined dataSets provider for AdminUserRoles entity
 */
class AdminUserRole extends AbstractRepository
{
    /**
     * @constructor
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'rolename' => 'RoleName%isolation%',
            'resource_access' => 'All'
        ];

        $this->_data['Administrators'] = [
            'rolename' => 'Administrators',
            'resource_access' => 'All',
            'role_id' => 1,
        ];

        $this->_data['role_sales'] = [
            'rolename' => 'RoleName%isolation%',
            'resource_access' => 'Custom',
            'roles_resources' => [
                'Sales' => 'Magento_Sales::sales',
                'Operation' => 'Magento_Sales::sales_operation',
                'Actions' => 'Magento_Sales::actions',
                'Orders' => 'Magento_Sales::sales_order',
                'Create' => 'Magento_Sales::create',
                'View' => 'Magento_Sales::actions_view',
                'Send Order Email' => 'Magento_Sales::email',
                'Reorder' => 'Magento_Sales::reorder',
                'Edit' => 'Magento_Sales::actions_edit',
                'Cancel' => 'Magento_Sales::cancel',
                'Accept or Deny Payment' => 'Magento_Sales::review_payment',
                'Capture' => 'Magento_Sales::capture',
                'Invoice' => 'Magento_Sales::invoice',
                'Credit Memos' => 'Magento_Sales::creditmemo',
                'Hold' => 'Magento_Sales::hold',
                'Unhold' => 'Magento_Sales::unhold',
                'Ship' => 'Magento_Sales::ship',
                'Comment' => 'Magento_Sales::comment',
                'Send Sales Emails' => 'Magento_Sales::emails',
            ]
        ];
    }
}
