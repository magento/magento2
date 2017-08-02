<?php
/**
 * Event configuration model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event\Config\Data;

/**
 * Class \Magento\Framework\Event\Config
 *
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * Modules configuration model
     *
     * @var Data
     * @since 2.0.0
     */
    protected $_dataContainer;

    /**
     * @param Data $dataContainer
     * @since 2.0.0
     */
    public function __construct(Data $dataContainer)
    {
        $this->_dataContainer = $dataContainer;
    }

    /**
     * Get observers by event name
     *
     * @param string $eventName
     * @return null|array|mixed
     * @since 2.0.0
     */
    public function getObservers($eventName)
    {
        return $this->_dataContainer->get($eventName, []);
    }
}
