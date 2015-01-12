<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

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
