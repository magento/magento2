<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;

/**
 * Configuration data validator of specific sections in the deployment configuration files.
 */
class Validator
{
    /**
     * Hash storage.
     *
     * @var Hash
     */
    private $configHash;

    /**
     * Hash generator of config data.
     *
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * Config data collector of specific sections.
     *
     * @var DataCollector
     */
    private $dataConfigCollector;

    /**
     * @param Hash $configHash the hash storage
     * @param HashGenerator $hashGenerator the hash generator of config data
     * @param DataCollector $dataConfigCollector the config data collector of specific sections
     */
    public function __construct(
        Hash $configHash,
        HashGenerator $hashGenerator,
        DataCollector $dataConfigCollector
    ) {
        $this->configHash = $configHash;
        $this->hashGenerator = $hashGenerator;
        $this->dataConfigCollector = $dataConfigCollector;
    }

    /**
     * Checks if config data in the deployment configuration files is valid.
     *
     * If the section name is set checks only its configuration data.
     * If config data is empty always returns true because it means that nothing to import.
     *
     * @param string $sectionName is name section for check data of the specific section
     * @return bool
     */
    public function isValid($sectionName = null)
    {
        $configs = $this->dataConfigCollector->getConfig($sectionName);
        $hashes = $this->configHash->get();

        foreach ($configs as $section => $config) {
            $hash = isset($hashes[$section]) ? $hashes[$section] : null;
            if ($config && $this->hashGenerator->generate($config) !== $hash) {
                return false;
            }
        }

        return true;
    }
}
