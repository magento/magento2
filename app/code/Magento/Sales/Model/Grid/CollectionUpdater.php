<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Grid;

class CollectionUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryManager;

    /**
     * @param \Magento\Framework\Registry $registryManager
     */
    public function __construct(\Magento\Framework\Registry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    /**
     * Update grid collection according to chosen order
     *
     * @param \Magento\Sales\Model\Resource\Transaction\Grid\Collection $argument
     * @return \Magento\Sales\Model\Resource\Transaction\Grid\Collection
     */
    public function update($argument)
    {
        $order = $this->registryManager->registry('current_order');
        if ($order) {
            $argument->setOrderFilter($order->getId());
        }
        $argument->addOrderInformation(['increment_id']);

        return $argument;
    }
}
