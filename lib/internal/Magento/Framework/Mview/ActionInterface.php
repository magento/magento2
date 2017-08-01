<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

/**
 * Interface \Magento\Framework\Mview\ActionInterface
 *
 * @since 2.0.0
 */
interface ActionInterface
{
    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     * @since 2.0.0
     */
    public function execute($ids);
}
