<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Action;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class Dummy implements ActionInterface, MviewActionInterface
{
    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeList(array $ids)
    {
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeRow($id)
    {
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids)
    {
    }
}
