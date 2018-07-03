<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use \Magento\Catalog\Model\Indexer\Product\Price\UpdateIndexInterface;

class CustomerGroup
{
    /**
     * @var UpdateIndexInterface
     */
    private $updateIndex;

    /**
     * Constructor
     *
     * @param UpdateIndexInterface $updateIndex
     */
    public function __construct(
        UpdateIndexInterface $updateIndex
    ) {
        $this->updateIndex = $updateIndex;
    }

    /**
     * Update price index after customer group saved
     *
     * @param GroupRepositoryInterface $subject
     * @param \Closure $proceed
     * @param GroupInterface $result
     * @return GroupInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        GroupRepositoryInterface $subject,
        \Closure $proceed,
        GroupInterface $group
    ) {
        $isGroupNew = !$group->getId();
        $group = $proceed($group);
        $this->updateIndex->update($group, $isGroupNew);
        return $group;
    }
}
