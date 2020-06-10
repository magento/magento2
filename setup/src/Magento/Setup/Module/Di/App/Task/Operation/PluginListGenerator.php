<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Framework\Config\ScopeInterface;
use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Framework\Interception\ConfigWriterInterface;

/**
 * Writes plugins configuration data per scope to generated metadata files.
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
        // remove primary scope for production mode
        $scopes = array_diff($scopes, ['primary']); // it does not reindex array

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
