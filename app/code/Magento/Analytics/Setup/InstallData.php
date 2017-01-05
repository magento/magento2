<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\Flag\FlagResource;
use Magento\Integration\Model\IntegrationService;
use Magento\Config\Model\Config;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    const MAGENTO_API_USER_NAME_PATH = 'analytics/integration_name';

    /**
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * @var FlagResource
     */
    private $flagResource;

    /**
     * @var IntegrationService
     */
    private $integrationService;

    /**
     * @var Config
     */
    public $config;

    /**
     * InstallData constructor.
     * @param NotificationTime $notificationTime
     * @param FlagResource $flagResource
     * @param IntegrationService $integrationService
     * @param Config $config
     */
    public function __construct(
        NotificationTime $notificationTime,
        FlagResource $flagResource,
        IntegrationService $integrationService,
        Config $config
    ) {
        $this->notificationTime = $notificationTime;
        $this->flagResource = $flagResource;
        $this->integrationService = $integrationService;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->notificationTime->storeLastTimeNotification(1);
        $this->integrationService->create($this->getIntegrationData());
    }

    /**
     * @return array
     */
    private function getIntegrationData()
    {
        $integrationData['name'] = $this->config->getConfigDataValue(self::MAGENTO_API_USER_NAME_PATH);
        $integrationData['all_resources'] = false;
        $integrationData['resource'][] = "Magento_Analytics::analytics";
        $integrationData['resource'][] = "Magento_Analytics::analytics_api";
        return $integrationData;
    }

}
