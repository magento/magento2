<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup
 *
 */
class CustomerGroup extends AbstractPlugin
{
    /**
     * Invalidate the indexer after the group is saved.
     *
     * @param GroupRepositoryInterface $subject
     * @param string                   $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(GroupRepositoryInterface $subject, $result)
    {
        $this->invalidateIndexer();
        return $result;
    }

    /**
     * Invalidate the indexer after the group is deleted.
     *
     * @param GroupRepositoryInterface $subject
     * @param string                   $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(GroupRepositoryInterface $subject, $result)
    {
        $this->invalidateIndexer();
        return $result;
    }

    /**
     * Invalidate the indexer after the group is deleted.
     *
     * @param GroupRepositoryInterface $subject
     * @param string                   $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(GroupRepositoryInterface $subject, $result)
    {
        $this->invalidateIndexer();
        return $result;
    }
}
