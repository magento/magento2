<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview;

/**
 * Interface \Magento\Framework\Mview\ProcessorInterface
 *
 * @api
 */
interface ProcessorInterface
{
    /**
     * Materialize all views by group (all views if empty)
     *
     * @param string $group
     * @return void
     */
    public function update($group = '');

    /**
     * Clear all views' changelogs by group (all views if empty)
     *
     * @param string $group
     * @return void
     */
    public function clearChangelog($group = '');
}
