<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Model\Logger\Handler;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Enable/disable syslog logging based on the store config setting.
 */
class Syslog extends \Magento\Framework\Logger\Handler\Syslog
{
    public const CONFIG_PATH = 'dev/syslog/syslog_logging';

    /**
     * Scope config.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Deployment config.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig Scope config
     * @param DeploymentConfig $deploymentConfig Deployment config
     * @param string $ident The string ident to be added to each message
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DeploymentConfig $deploymentConfig,
        string $ident
    ) {
        parent::__construct($ident);

        $this->scopeConfig = $scopeConfig;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function isHandling(array $record): bool
    {
        return parent::isHandling($record)
            && $this->deploymentConfig->isAvailable()
            && $this->scopeConfig->getValue(self::CONFIG_PATH);
    }
}
