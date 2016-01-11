<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Setup;

use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Model\Config;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class Recurring
 *
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var ConfigBasedIntegrationManager
     */
    private $integrationManager;

    /**
     * @var Config
     */
    private $integrationConfig;

    /**
     * Initialize dependencies
     *
     * @param ConfigBasedIntegrationManager $integrationManager
     * @param Config $integrationConfig
     */
    public function __construct(
        ConfigBasedIntegrationManager $integrationManager,
        Config $integrationConfig
    ) {
        $this->integrationManager = $integrationManager;
        $this->integrationConfig = $integrationConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->integrationManager->processIntegrationConfig($this->integrationConfig->getIntegrations());
    }
}
