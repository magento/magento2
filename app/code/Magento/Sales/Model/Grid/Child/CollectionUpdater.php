<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Grid\Child;

/**
 * Class \Magento\Sales\Model\Grid\Child\CollectionUpdater
 *
 * @since 2.0.0
 */
class CollectionUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registryManager;

    /**
     * @param \Magento\Framework\Registry $registryManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Registry $registryManager
    ) {
        $this->registryManager = $registryManager;
    }

    /**
     * Update grid collection according to chosen transaction
     *
     * @param \Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection $argument
     * @return \Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection
     * @since 2.0.0
     */
    public function update($argument)
    {
        $argument->addParentIdFilter($this->registryManager->registry('current_transaction')->getId());
        return $argument;
    }
}
