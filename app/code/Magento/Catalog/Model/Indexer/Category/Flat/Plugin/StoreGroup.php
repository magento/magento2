<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

class StoreGroup
{
    /** @var \Magento\Indexer\Model\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $state;

    /**
     * @param \Magento\Indexer\Model\IndexerRegistry $indexerRegistry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $state
     */
    public function __construct(
        \Magento\Indexer\Model\IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $state
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->state = $state;
    }

    /**
     * Validate changes for invalidating indexer
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return bool
     */
    protected function validate(\Magento\Framework\Model\AbstractModel $group)
    {
        return $group->dataHasChangedFor('root_category_id') && !$group->isObjectNew();
    }

    /**
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $group
     *
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Framework\Model\Resource\Db\AbstractDb $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $group
    ) {
        $needInvalidating = $this->validate($group);
        $objectResource = $proceed($group);
        if ($needInvalidating && $this->state->isFlatEnabled()) {
            $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID)->invalidate();
        }

        return $objectResource;
    }
}
