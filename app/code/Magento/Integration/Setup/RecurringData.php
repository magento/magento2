<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Model\ConsolidatedConfig;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class Recurring
 *
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var ConfigBasedIntegrationManager
     */
    private $integrationManager;

    /**
     * @var ConsolidatedConfig
     */
    private $integrationConfig;

    /**
     * Initialize dependencies
     *
     * @param ConfigBasedIntegrationManager $integrationManager
     * @param ConsolidatedConfig $integrationConfig
     */
    public function __construct(
        ConfigBasedIntegrationManager $integrationManager,
        ConsolidatedConfig $integrationConfig
    ) {
        $this->integrationManager = $integrationManager;
        $this->integrationConfig = $integrationConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->integrationManager->processConfigBasedIntegrations($this->integrationConfig->getIntegrations());
    }
}
