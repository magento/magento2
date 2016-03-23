<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

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
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $configData
     * @param bool $rollback
     */
    public function __construct(FixtureFactory $fixtureFactory, $configData = null, $rollback = false)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->configData = $configData;
        $this->rollback = $rollback;
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
            $config = $this->fixtureFactory->createByCode('configData', ['dataset' => $configDataSet . $prefix]);
            if ($config->hasData('section')) {
                $config->persist();
                $result[] = $config;
            }
        }

        return ['config' => $result];
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
