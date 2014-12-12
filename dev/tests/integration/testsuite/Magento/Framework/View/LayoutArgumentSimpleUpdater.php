<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
