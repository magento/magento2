<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Model\Indexer\Category\Flat\State;

class StoreGroup
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param State $state
     */
    public function __construct(IndexerRegistry $indexerRegistry, State $state)
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->state = $state;
    }

    /**
     * Validate changes for invalidating indexer
     *
     * @param AbstractModel $group
     * @return bool
     */
    protected function validate(AbstractModel $group)
    {
        return $group->dataHasChangedFor('root_category_id') && !$group->isObjectNew();
    }

    /**
     * Invalidate flat category indexer if root category changed for store group
     *
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     * @param AbstractModel $group
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(AbstractDb $subject, AbstractDb $objectResource, AbstractModel $group)
    {
        $needInvalidating = $this->validate($group);
        if ($needInvalidating && $this->state->isFlatEnabled()) {
            $this->indexerRegistry->get(State::INDEXER_ID)->invalidate();
        }

        return $objectResource;
    }
}
