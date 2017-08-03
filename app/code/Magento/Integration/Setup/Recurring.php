<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Setup;

use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Model\ConsolidatedConfig;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class Recurring
 *
 * @since 2.1.0
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var ConfigBasedIntegrationManager
     * @since 2.1.0
     */
    private $integrationManager;

    /**
     * @var ConsolidatedConfig
     * @since 2.1.0
     */
    private $integrationConfig;

    /**
     * Initialize dependencies
     *
     * @param ConfigBasedIntegrationManager $integrationManager
     * @param ConsolidatedConfig $integrationConfig
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->integrationManager->processConfigBasedIntegrations($this->integrationConfig->getIntegrations());
    }
}
