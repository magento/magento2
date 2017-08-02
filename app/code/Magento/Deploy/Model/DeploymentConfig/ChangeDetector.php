<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;

/**
 * Configuration data changes detector.
 *
 * Detects changes in specific sections of the deployment configuration files.
 * This detector checks that configuration data from sections was not changed.
 * @since 2.2.0
 */
class ChangeDetector
{
    /**
     * Hash storage.
     *
     * @var Hash
     * @since 2.2.0
     */
    private $configHash;

    /**
     * Hash generator of config data.
     *
     * @var HashGenerator
     * @since 2.2.0
     */
    private $hashGenerator;

    /**
     * Config data collector of specific sections.
     *
     * @var DataCollector
     * @since 2.2.0
     */
    private $dataConfigCollector;

    /**
     * @param Hash $configHash The hash storage
     * @param HashGenerator $hashGenerator The hash generator of config data
     * @param DataCollector $dataConfigCollector The config data collector of specific sections
     * @since 2.2.0
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
     * Checks if config data in the deployment configuration files was changed.
     *
     * Checks if config data was changed based on its hash.
     * If the new hash of config data and the saved hash are different returns true.
     * If config data is empty always returns false.
     * In the other cases returns false.
     *
     * @param string $sectionName The section name for check data of the specific section
     * @return bool
     * @since 2.2.0
     */
    public function hasChanges($sectionName = null)
    {
        $configs = $this->dataConfigCollector->getConfig($sectionName);
        $hashes = $this->configHash->get();

        foreach ($configs as $section => $config) {
            if (null === $config) {
                continue;
            }
            $savedHash = isset($hashes[$section]) ? $hashes[$section] : null;
            $generatedHash = empty($config) && !$savedHash ? null : $this->hashGenerator->generate($config);
            if ($generatedHash !== $savedHash) {
                return true;
            }
        }

        return false;
    }
}
