<?php
/**
 * Event configuration model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event\Config\Data;

class Config implements ConfigInterface
{
    /**
     * Modules configuration model
     *
     * @var Data
     */
    protected $_dataContainer;

    /**
     * @param Data $dataContainer
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
     */
    public function getObservers($eventName)
    {
        return $this->_dataContainer->get($eventName, []);
    }
}
