<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Bootstrap;

use Magento\TestFramework\Annotation;
use Magento\TestFramework\Application;

/**
 * Bootstrap of the custom DocBlock annotations
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DocBlock
{
    /**
     * @var string
     */
    protected $_fixturesBaseDir;

    /**
     * @param string $fixturesBaseDir
     */
    public function __construct($fixturesBaseDir)
    {
        $this->_fixturesBaseDir = $fixturesBaseDir;
    }

    /**
     * Activate custom DocBlock annotations along with more-or-less permanent workarounds
     *
     * @param Application $application
     */
    public function registerAnnotations(Application $application)
    {
        $eventManager = new \Magento\TestFramework\EventManager($this->_getSubscribers($application));
        \Magento\TestFramework\Event\PhpUnit::setDefaultEventManager($eventManager);
        \Magento\TestFramework\Event\Magento::setDefaultEventManager($eventManager);
    }

    /**
     * Get list of subscribers.
     *
     * Note: order of registering (and applying) annotations is important.
     * To allow config fixtures to deal with fixture stores, data fixtures should be processed first.
     * ConfigFixture applied twice because data fixtures could clean config and clean custom settings
     *
     * @param Application $application
     * @return array
     */
    protected function _getSubscribers(Application $application)
    {
        return [
            new \Magento\TestFramework\Workaround\Segfault(),
            new \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties(),
            new \Magento\TestFramework\Workaround\Cleanup\StaticProperties(),
            new \Magento\TestFramework\Isolation\WorkingDirectory(),
            new \Magento\TestFramework\Isolation\DeploymentConfig(),
            new \Magento\TestFramework\Workaround\Override\Fixture\Resolver\TestSetter(),
            new Annotation\AppIsolation($application),
            new Annotation\ComponentRegistrarFixture(
                $this->_fixturesBaseDir,
                $application
            ),
            new Annotation\IndexerDimensionMode(),
            new \Magento\TestFramework\Isolation\AppConfig(),
            new Annotation\ConfigFixture(),
            new Annotation\DataFixtureBeforeTransaction(),
            new \Magento\TestFramework\Event\Transaction(
                new \Magento\TestFramework\EventManager(
                    [
                        new Annotation\DbIsolation(),
                        new Annotation\DataFixture(),
                    ]
                )
            ),
            new Annotation\AppArea($application),
            new Annotation\Cache(),
            new Annotation\AdminConfigFixture(),
            new Annotation\ConfigFixture(),
        ];
    }
}
