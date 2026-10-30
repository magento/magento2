<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Indexer;

/**
 * @api Implement custom Action Interface
 * @since 100.0.2
 */
interface ActionInterface
{
    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull();

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id);
}
