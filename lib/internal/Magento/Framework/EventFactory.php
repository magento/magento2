<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Class \Magento\Framework\EventFactory
 *
 * @since 2.0.0
 */
class EventFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param array $arguments
     * @return Event
     * @since 2.0.0
     */
    public function create($arguments = [])
    {
        return $this->_objectManager->create(Event::class, $arguments);
    }
}
