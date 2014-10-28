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

namespace Magento\Webapi\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Authorization\RoleLocator;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\Resource\Role\CollectionFactory as RoleCollectionFactory;

class WebapiRoleLocator implements RoleLocator
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var RoleCollectionFactory
     */
    protected $roleCollectionFactory;

    /**
     * Constructs a role locator using the user context.
     *
     * @param UserContextInterface $userContext
     * @param RoleCollectionFactory $roleCollectionFactory
     */
    public function __construct(
        UserContextInterface $userContext,
        RoleCollectionFactory $roleCollectionFactory
    ) {
        $this->userContext = $userContext;
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclRoleId()
    {
        $userId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();

        $roleCollection = $this->roleCollectionFactory->create();
        /** @var Role $role */
        $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();

        if (!$role->getId()) {
            return null;
        }

        return $role->getId();
    }
}
