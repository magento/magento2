<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 */
class Recurring implements InstallSchemaInterface
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
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->integrationManager->processConfigBasedIntegrations($this->integrationConfig->getIntegrations());
    }
}
