<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;

/**
 * Configuration data validator of specific sections in the deployment configuration files.
 *
 * This validator checks that configuration data from specific sections was not changed.
 * If the data was changed validator returns false.
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
     * Checks if config data was changed based on its hash.
     * If the new hash of config data and the saved hash are different returns false.
     * If config data is empty always returns true.
     * In the other cases returns true.
     *
     * @return bool
     */
    public function isValid()
    {
        $config = $this->dataConfigCollector->getConfig();

        if (!$config) {
            return true;
        }

        return $this->hashGenerator->generate($config) === $this->configHash->get();
    }
}
