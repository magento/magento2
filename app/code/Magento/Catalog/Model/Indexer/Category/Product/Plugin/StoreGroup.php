<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

class StoreGroup
{
    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(\Magento\Framework\Indexer\IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $group
    ) {
        $needInvalidating = $this->validate($group);
        $objectResource = $proceed($group);
        if ($needInvalidating) {
            $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID)->invalidate();
        }

        return $objectResource;
    }

    /**
     * Validate changes for invalidating indexer
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return bool
     */
    protected function validate(\Magento\Framework\Model\AbstractModel $group)
    {
        return ($group->dataHasChangedFor(
            'website_id'
        ) || $group->dataHasChangedFor(
            'root_category_id'
        )) && !$group->isObjectNew();
    }
}
