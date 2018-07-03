<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Mtf\App\State;

use Magento\Mtf\App\State\AbstractState;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\App\State\StateHandlerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class NotificationHandler
 */
class NotificationHandler implements StateHandlerInterface
{
    /**
     * @var DataInterface
     */
    private $configuration;

    /**
     * NotificationHandler constructor.
     *
     * @param DataInterface $configuration
     */
    public function __construct(DataInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Mark admin user as notified about release notes.
     *
     * @param AbstractState $state
     * @return bool
     * @throws \Exception
     * @SuppressWarnings("unused")
     */
    public function execute(AbstractState $state)
    {
        $url = $_ENV['app_backend_url'] . 'admin/releaseNotification/notification/markUserNotified/?isAjax=true';
        $curl = new BackendDecorator(new CurlTransport(), $this->configuration);
        $curl->write($url, []);
        $response = json_decode($curl->read(), true);
        $curl->close();
        if (isset($response['success'])) {
            return $response['success'];
        }
        return false;
    }
}
