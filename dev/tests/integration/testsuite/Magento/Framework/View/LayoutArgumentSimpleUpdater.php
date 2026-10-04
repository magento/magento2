<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
