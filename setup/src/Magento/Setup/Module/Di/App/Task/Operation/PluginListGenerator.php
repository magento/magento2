<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Framework\Config\ScopeInterface;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Framework\Interception\ConfigWriterInterface;

/**
 * Writes plugin list configuration data per scope to generated metadata.
 */
class PluginListGenerator implements OperationInterface
{
    /**
     * @var ScopeInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigWriterInterface
     */
    private $configWriter;

    /**
     * @param ScopeInterface $scopeConfig
     * @param ConfigWriterInterface $configWriter
     */
    public function __construct(
        ScopeInterface $scopeConfig,
        ConfigWriterInterface $configWriter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritDoc
     */
    public function doOperation()
    {
        $scopes = $this->scopeConfig->getAllScopes();
        // Cache IDs are now normalized (sorted) in PluginListGenerator::write()
        // so processing order no longer affects cache ID generation
        $this->configWriter->write($scopes);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Plugin list generation';
    }
}
