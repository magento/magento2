<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\Event\ManagerInterface;

/**
 * Class EventManager
 * @since 2.1.0
 */
class EventManager
{
    /**
     * @var ManagerInterface
     * @since 2.1.0
     */
    private $eventManager;

    /**
     * EventManager constructor.
     * @param ManagerInterface $eventManager
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function dispatch($eventName, array $data = [])
    {
        $this->eventManager->dispatch($eventName, $data);
    }
}
