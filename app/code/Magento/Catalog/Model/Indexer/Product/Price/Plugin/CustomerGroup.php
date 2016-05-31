<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;

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
