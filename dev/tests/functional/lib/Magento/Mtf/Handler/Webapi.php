<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Handler;

use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Abstract class for webapi handlers.
 */
abstract class Webapi extends Curl implements HandlerInterface
{
    /**
     * Configuration parameters array.
     *
     * @var DataInterface
     */
    protected $configuration;

    /**
     * Event Manager.
     *
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Curl transport for webapi.
     *
     * @var WebapiDecorator.
     */
    protected $webapiTransport;

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param WebapiDecorator $webapiTransport
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        WebapiDecorator $webapiTransport
    ) {
        $this->configuration = $configuration;
        $this->eventManager = $eventManager;
        $this->webapiTransport = $webapiTransport;
    }
}
