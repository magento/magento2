<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

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
     * @param AbstractDb $result
     * @param AbstractModel $group
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(AbstractDb $subject, AbstractDb $result, AbstractModel $group)
    {
        if ($this->validate($group) && $this->state->isFlatEnabled()) {
            $this->indexerRegistry->get(State::INDEXER_ID)->invalidate();
        }

        return $result;
    }
}
