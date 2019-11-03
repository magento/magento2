<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessageQueue\Model\Plugin\ResourceModel;

/**
 * Lock plugin to clear queue upon maintenance mode turning off.
 */
class Lock
{
    /**
     * @var \Magento\Framework\MessageQueue\Lock\WriterInterface
     */
    private $lock;

    /**
     * Lock constructor.
     *
     * @param \Magento\Framework\MessageQueue\Lock\WriterInterface $lock
     */
    public function __construct(\Magento\Framework\MessageQueue\Lock\WriterInterface $lock)
    {
        $this->lock = $lock;
    }

    /**
     * When maintenance mode is turned off, lock queue should be cleared
     *
     * @param \Magento\Framework\App\MaintenanceMode $subject
     * @param boolean $result
     * @return void
     */
    public function afterSet(\Magento\Framework\App\MaintenanceMode $subject, $result)
    {
        if (!$subject->isOn() && $result) {
            $this->lock->releaseOutdatedLocks();
        }
    }
}
