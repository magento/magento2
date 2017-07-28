<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * @api Implement custom Action Interface
 * @since 2.0.0
 */
interface ActionInterface
{
    /**
     * Execute full indexation
     *
     * @return void
     * @since 2.0.0
     */
    public function executeFull();

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @since 2.0.0
     */
    public function executeList(array $ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @since 2.0.0
     */
    public function executeRow($id);
}
