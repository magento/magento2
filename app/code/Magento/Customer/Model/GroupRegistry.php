<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Registry for Customer Group models
 */
class GroupRegistry implements ResetAfterRequestInterface
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
        if ($group->getId() === null || $group->getId() != $groupId) {
            throw NoSuchEntityException::singleField(GroupInterface::ID, $groupId);
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->registry = [];
    }
}
