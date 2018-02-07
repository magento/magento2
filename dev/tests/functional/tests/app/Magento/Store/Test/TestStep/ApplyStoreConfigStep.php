<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestStep;

use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Store\Test\Fixture\Store;

/**
 * Apply Store Config.
 */
class ApplyStoreConfigStep implements TestStepInterface
{
    /**
     * Fixture creation factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Fixture Store.
     *
     * @var Store
     */
    private $store;

    /**
     * Store specific config data.
     *
     * @var string
     */
    private $storeConfigData;

    /**
     * Rollback.
     *
     * @var bool
     */
    private $rollback;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param Store $store
     * @param string $storeConfig
     * @param bool $rollback
     */
    public function __construct(FixtureFactory $fixtureFactory, Store $store, $storeConfig, $rollback = false)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->store = $store;
        $this->storeConfigData = $storeConfig;
        $this->rollback = $rollback;
    }

    /**
     * Apply Custom Store config.
     *
     * @return void
     */
    public function run()
    {
        $configData = array_map('trim', explode(',', $this->storeConfigData));
        $prefix = ($this->rollback == false) ? '' : '_rollback';

        foreach ($configData as $configDataSet) {
            /** @var ConfigData $config */
            $config = $this->fixtureFactory->createByCode('configData', ['dataset' => $configDataSet . $prefix]);
            if ($config->hasData('section')) {
                $data = array_merge(
                    ['scope' => ['fixture' => $this->store, 'scope_type' => 'store', 'set_level' => 'store']],
                    $config->getSection()
                );

                $config = $this->fixtureFactory->createByCode('configData', ['data' => $data]);
                $config->persist();
            }
        }
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
