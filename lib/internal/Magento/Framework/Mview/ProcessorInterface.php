<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

/**
 * Interface \Magento\Framework\Mview\ProcessorInterface
 *
 * @since 2.0.0
 */
interface ProcessorInterface
{
    /**
     * Materialize all views by group (all views if empty)
     *
     * @param string $group
     * @return void
     * @since 2.0.0
     */
    public function update($group = '');

    /**
     * Clear all views' changelogs by group (all views if empty)
     *
     * @param string $group
     * @return void
     * @since 2.0.0
     */
    public function clearChangelog($group = '');
}
