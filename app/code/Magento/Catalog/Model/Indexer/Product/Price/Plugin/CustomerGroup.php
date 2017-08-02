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
 * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function afterDeleteById(GroupRepositoryInterface $subject, $result)
    {
        $this->invalidateIndexer();
        return $result;
    }
}
