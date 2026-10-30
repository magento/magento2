<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;

/**
 * Saves and Retrieves deployment configuration hash.
 *
 * This hash keeps version of last imported data. Hash is used to define whether data was updated
 * and import is required.
 *
 * @see \Magento\Deploy\Model\DeploymentConfig\ChangeDetector::hasChanges()
 */
class Hash
{
    /**
     * Name of the section where deployment configuration hash is stored.
     */
    public const CONFIG_KEY = 'config_hash';

    /**
     * Cache tag for successful deployment configuration checks.
     */
    public const CACHE_TAG = 'DEPLOYMENT_CONFIG_HASH';

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
     * Flag Resource model.
     *
     * @var FlagResource
     */
    private $flagResource;

    /**
     * Factory class for \Magento\Framework\Flag
     *
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @param Hash\Generator $configHashGenerator the hash generator
     * @param DataCollector $dataConfigCollector the config data collector
     * @param FlagResource $flagResource
     * @param FlagFactory $flagFactory
     * @param CacheInterface|null $cache
     */
    public function __construct(
        Hash\Generator $configHashGenerator,
        DataCollector $dataConfigCollector,
        FlagResource $flagResource,
        FlagFactory $flagFactory,
        ?CacheInterface $cache = null
    ) {
        $this->configHashGenerator = $configHashGenerator;
        $this->dataConfigCollector = $dataConfigCollector;
        $this->flagResource = $flagResource;
        $this->flagFactory = $flagFactory;
        $this->cache = $cache ?? ObjectManager::getInstance()->get(CacheInterface::class);
    }

    /**
     * Updates hash in the storage.
     *
     * If the specific section name is set, then hash will be updated only for this section,
     * in another case hash will be updated for all sections which defined in di.xml
     * The hash is generated based on data from configuration files.
     *
     * @param string $sectionName the specific section name
     * @return void
     * @throws LocalizedException is thrown when hash was not saved
     */
    public function regenerate($sectionName = null)
    {
        try {
            $hashes = $this->get();
            $configs = $this->dataConfigCollector->getConfig($sectionName);

            foreach ($configs as $section => $config) {
                $hashes[$section] = $this->configHashGenerator->generate($config);
            }

            /** @var Flag $flag */
            $flag = $this->getFlagObject();
            $flag->setFlagData($hashes);
            $this->flagResource->save($flag);
        } catch (\Exception $exception) {
            throw new LocalizedException(__("The hash isn't saved."), $exception);
        }

        $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
    }

    /**
     * Retrieves saved hashes from storage.
     *
     * @return array
     */
    public function get()
    {
        /** @var Flag $flag */
        $flag = $this->getFlagObject();
        return (array) ($flag->getFlagData() ?: []);
    }

    /**
     * Returns flag object.
     *
     * We use it for saving hashes of sections in the DB.
     *
     * @return Flag
     */
    private function getFlagObject()
    {
        /** @var Flag $flag */
        $flag = $this->flagFactory
            ->create(['data' => ['flag_code' => self::CONFIG_KEY]]);
        $this->flagResource->load($flag, self::CONFIG_KEY, 'flag_code');
        return $flag;
    }
}
