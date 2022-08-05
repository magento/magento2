<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Active subscription process for Advanced Reporting
 */
class PrepareInitialConfig implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SubscriptionHandler
     */
    private $subscriptionHandler;

    /**
     * @var string
     */
    private $subscriptionEnabledConfigPath = 'analytics/subscription/enabled';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SubscriptionHandler $subscriptionHandler
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SubscriptionHandler $subscriptionHandler
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->subscriptionHandler = $subscriptionHandler;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path' => $this->subscriptionEnabledConfigPath,
                'value' => Enabledisable::ENABLE_VALUE,
            ]
        );

        $this->subscriptionHandler->processEnabled();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
