<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View;

/**
 * Dummy layout argument updater model
 */
class LayoutArgumentObjectUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * Update specified argument
     *
     * @param \Magento\Framework\Data\Collection $argument
     * @return \Magento\Framework\Data\Collection
     */
    public function update($argument)
    {
        $calls = $argument->getUpdaterCall();
        $calls[] = 'updater call';
        $argument->setUpdaterCall($calls);
        return $argument;
    }
}
