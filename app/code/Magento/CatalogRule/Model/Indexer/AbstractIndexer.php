<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer;

use Magento\CatalogRule\CatalogRuleException;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Indexer\Model\ActionInterface as IndexerActionInterface;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @param IndexBuilder $indexBuilder
     */
    public function __construct(IndexBuilder $indexBuilder)
    {
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @throws CatalogRuleException
     * @return void
     */
    public function executeList(array $ids)
    {
        if (!$ids) {
            throw new CatalogRuleException(__('Could not rebuild index for empty products array'));
        }
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID list. Template method
     *
     * @param int[] $ids
     * @return void
     */
    abstract protected function doExecuteList($ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws CatalogRuleException
     * @return void
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new CatalogRuleException(__('Could not rebuild index for undefined product'));
        }
        $this->doExecuteRow($id);
    }

    /**
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     * @throws \Magento\CatalogRule\CatalogRuleException
     * @return void
     */
    abstract protected function doExecuteRow($id);
}
