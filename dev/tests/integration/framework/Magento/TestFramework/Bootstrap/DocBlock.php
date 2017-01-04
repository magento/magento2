<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Bootstrap;

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
     */
    public function registerAnnotations(\Magento\TestFramework\Application $application)
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
     * @param \Magento\TestFramework\Application $application
     * @return array
     */
    protected function _getSubscribers(\Magento\TestFramework\Application $application)
    {
        return [
            new \Magento\TestFramework\Workaround\Segfault(),
            new \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties(),
            new \Magento\TestFramework\Workaround\Cleanup\StaticProperties(),
            new \Magento\TestFramework\Isolation\WorkingDirectory(),
            new \Magento\TestFramework\Isolation\DeploymentConfig(),
            new \Magento\TestFramework\Annotation\AppIsolation($application),
            new \Magento\TestFramework\Isolation\AppConfig(),
            new \Magento\TestFramework\Annotation\ConfigFixture(),
            new \Magento\TestFramework\Annotation\DataFixtureBeforeTransaction($this->_fixturesBaseDir),
            new \Magento\TestFramework\Event\Transaction(
                new \Magento\TestFramework\EventManager(
                    [
                        new \Magento\TestFramework\Annotation\DbIsolation(),
                        new \Magento\TestFramework\Annotation\DataFixture($this->_fixturesBaseDir),
                    ]
                )
            ),
            new \Magento\TestFramework\Annotation\ComponentRegistrarFixture($this->_fixturesBaseDir),
            new \Magento\TestFramework\Annotation\AppArea($application),
            new \Magento\TestFramework\Annotation\Cache($application),
            new \Magento\TestFramework\Annotation\AdminConfigFixture(),
            new \Magento\TestFramework\Annotation\ConfigFixture(),
        ];
    }
}
