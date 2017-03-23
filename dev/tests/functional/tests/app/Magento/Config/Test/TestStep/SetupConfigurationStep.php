<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\TestStep;

use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Util\Command\Cli\Cache;

/**
 * Setup configuration using handler.
 */
class SetupConfigurationStep implements TestStepInterface
{
    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Rollback.
     *
     * @var bool
     */
    protected $rollback;

    /**
     * Flush cache.
     *
     * @var bool
     */
    protected $flushCache;

    /**
     * Cli command to do operations with cache.
     *
     * @var Cache
     */
    private $cache;

    /**
     * Preparing step properties.
     *
     * @param FixtureFactory $fixtureFactory
     * @param Cache $cache
     * @param string $configData
     * @param bool $rollback
     * @param bool $flushCache
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        Cache $cache,
        $configData = null,
        $rollback = false,
        $flushCache = false
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->configData = $configData;
        $this->rollback = $rollback;
        $this->flushCache = $flushCache;
        $this->cache = $cache;
    }

    /**
     * Set config.
     *
     * @return array
     */
    public function run()
    {
        if ($this->configData === null) {
            return [];
        }
        $prefix = ($this->rollback == false) ? '' : '_rollback';

        $configData = array_map('trim', explode(',', $this->configData));
        $result = [];

        foreach ($configData as $configDataSet) {
            /** @var ConfigData $config */
            $config = $this->fixtureFactory->createByCode('configData', ['dataset' => $configDataSet . $prefix]);
            if ($config->hasData('section')) {
                $config->persist();
                $result = array_merge($result, $config->getSection());
            }
            if ($this->flushCache) {
                $this->cache->flush();
            }
        }
        $config = $this->fixtureFactory->createByCode('configData', ['data' => $result]);

        return ['config' => $config];
    }

    /**
     * Rollback configuration.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->rollback = true;
        $this->run();
    }
}
