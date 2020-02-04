<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\Event\ManagerInterface;

/**
 * Class EventManager
 */
class EventManager
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * EventManager constructor.
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * Get entity prefix for event
     *
     * @param string $entityType
     * @return string
     */
    private function resolveEntityPrefix($entityType)
    {
        return strtolower(str_replace("\\", "_", $entityType));
    }

    /**
     * @param string $entityType
     * @param string $eventSuffix
     * @param array $data
     * @return void
     */
    public function dispatchEntityEvent($entityType, $eventSuffix, array $data = [])
    {
        $this->eventManager->dispatch(
            $this->resolveEntityPrefix($entityType) . '_' . $eventSuffix,
            $data
        );
    }

    /**
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function dispatch($eventName, array $data = [])
    {
        $this->eventManager->dispatch($eventName, $data);
    }
}
