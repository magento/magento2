<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
