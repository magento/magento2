<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var bool
     */
    private $needInvalidating;

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
     * Check if need invalidate flat category indexer
     *
     * @param AbstractDb $subject
     * @param AbstractModel $group
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(AbstractDb $subject, AbstractModel $group)
    {
        $this->needInvalidating = $this->validate($group);
    }

    /**
     * Invalidate flat category indexer if root category changed for store group
     *
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(AbstractDb $subject, AbstractDb $objectResource)
    {
        if ($this->needInvalidating && $this->state->isFlatEnabled()) {
            $this->indexerRegistry->get(State::INDEXER_ID)->invalidate();
        }

        return $objectResource;
    }
}
