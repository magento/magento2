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

 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\Group;

/**
 * Registry for Customer Group models
 */
class GroupRegistry
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @param GroupFactory $groupFactory
     */
    public function __construct(GroupFactory $groupFactory)
    {
        $this->groupFactory = $groupFactory;
    }

    /**
     * Get instance of the Group Model identified by an id
     *
     * @param int $groupId
     * @return Group
     * @throws NoSuchEntityException
     */
    public function retrieve($groupId)
    {
        if (isset($this->registry[$groupId])) {
            return $this->registry[$groupId];
        }
        $group = $this->groupFactory->create();
        $group->load($groupId);
        if (is_null($group->getId()) || $group->getId() != $groupId) {
            throw NoSuchEntityException::singleField('groupId', $groupId);
        }
        $this->registry[$groupId] = $group;
        return $group;
    }

    /**
     * Remove an instance of the Group Model from the registry
     *
     * @param int $groupId
     * @return void
     */
    public function remove($groupId)
    {
        unset($this->registry[$groupId]);
    }
}
