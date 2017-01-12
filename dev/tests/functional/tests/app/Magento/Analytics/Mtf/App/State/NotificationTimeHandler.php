<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Mtf\App\State;

use Magento\Mtf\App\State\AbstractState;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\App\State\StateHandlerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class NotificationTimeHandler
 */
class NotificationTimeHandler implements StateHandlerInterface
{
    /**
     * @var DataInterface
     */
    private $configuration;

    /**
     * NotificationTimeHandler constructor.
     *
     * @param DataInterface $configuration
     */
    public function __construct(
        DataInterface $configuration
    ) {
        $this->configuration = $configuration;
    }

    /**
     * Cancel subscription for functional tests
     *
     * @param AbstractState $state
     * @return bool
     * @throws \Exception
     */
    public function execute(AbstractState $state)
    {
        $url = $_ENV['app_backend_url'] . 'analytics/subscription/postpone';
        $curl = new BackendDecorator(new CurlTransport(), $this->configuration);
        $curl->write($url, []);
        $response = $curl->read();
        $curl->close();
        if (isset($response['success'])) {
            return $response['success'];
        }
        return false;
    }
}
