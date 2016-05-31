<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Dummy layout argument updater model
 */
class LayoutArgumentSimpleUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * Update specified argument
     *
     * @param int $argument
     * @return int
     */
    public function update($argument)
    {
        $argument++;
        return $argument;
    }
}
