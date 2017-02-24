<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\Exception\LocalizedException;

/**
 * Updates hash in the storage.
 */
class HashUpdater
{
    /**
     * Hash storage.
     *
     * @var Hash
     */
    private $configHash;

    /**
     * Hash generator.
     *
     * @var Hash\Generator
     */
    private $configHashGenerator;

    /**
     * Config data collector.
     *
     * @var DataCollector
     */
    private $dataConfigCollector;

    /**
     * @param Hash $configHash the hash storage
     * @param Hash\Generator $configHashGenerator the hash generator
     * @param DataCollector $dataConfigCollector the config data collector
     */
    public function __construct(
        Hash $configHash,
        Hash\Generator $configHashGenerator,
        DataCollector $dataConfigCollector
    ) {
        $this->configHash = $configHash;
        $this->configHashGenerator = $configHashGenerator;
        $this->dataConfigCollector = $dataConfigCollector;
    }

    /**
     * Updates hash in the storage.
     *
     * @return void
     * @throws LocalizedException
     */
    public function update()
    {
        try {
            $config = $this->dataConfigCollector->getConfig();
            $this->configHash->save($this->configHashGenerator->generate($config));
        } catch (LocalizedException $exception) {
            throw new LocalizedException(__('Hash has not been updated'), $exception);
        }
    }
}
