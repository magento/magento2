<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model;

use Magento\Downloadable\Api\DomainManagerInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer as ConfigWriter;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Manage downloadable domains whitelist in the environment config.
 */
class DomainManager implements DomainManagerInterface
{
    /**
     * Path to the allowed domains in the deployment config.
     */
    private const PARAM_DOWNLOADABLE_DOMAINS = 'downloadable_domains';

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ConfigWriter $configWriter
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ConfigWriter $configWriter,
        DeploymentConfig $deploymentConfig
    ) {
        $this->configWriter = $configWriter;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function getDomains(): array
    {
        return array_map('strtolower', $this->deploymentConfig->get(self::PARAM_DOWNLOADABLE_DOMAINS) ?? []);
    }

    /**
     * @inheritdoc
     */
    public function addDomains(array $hosts): void
    {
        $whitelist = $this->getDomains();
        foreach (array_map('strtolower', $hosts) as $host) {
            if (!in_array($host, $whitelist)) {
                $whitelist[] = $host;
            }
        }

        $this->configWriter->saveConfig(
            [
                ConfigFilePool::APP_ENV => [
                    self::PARAM_DOWNLOADABLE_DOMAINS => $whitelist,
                ],
            ],
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function removeDomains(array $hosts): void
    {
        $whitelist = $this->getDomains();
        foreach (array_map('strtolower', $hosts) as $host) {
            if (in_array($host, $whitelist)) {
                $index = array_search($host, $whitelist);
                unset($whitelist[$index]);
            }
        }

        $whitelist = array_values($whitelist);  // reindex whitelist to prevent non-sequential keying

        $this->configWriter->saveConfig(
            [
                ConfigFilePool::APP_ENV => [
                    self::PARAM_DOWNLOADABLE_DOMAINS => $whitelist,
                ],
            ],
            true
        );
    }
}
