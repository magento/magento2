<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     *
     * @param \Magento\TestFramework\Application $application
     * @return array
     */
    protected function _getSubscribers(\Magento\TestFramework\Application $application)
    {
        return array(
            new \Magento\TestFramework\Workaround\Segfault(),
            new \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties(),
            new \Magento\TestFramework\Workaround\Cleanup\StaticProperties(),
            new \Magento\TestFramework\Isolation\WorkingDirectory(),
            new \Magento\TestFramework\Annotation\AppIsolation($application),
            new \Magento\TestFramework\Event\Transaction(
                new \Magento\TestFramework\EventManager(
                    array(
                        new \Magento\TestFramework\Annotation\DbIsolation(),
                        new \Magento\TestFramework\Annotation\DataFixture($this->_fixturesBaseDir)
                    )
                )
            ),
            new \Magento\TestFramework\Annotation\AppArea($application),
            new \Magento\TestFramework\Annotation\ConfigFixture(),
            new \Magento\TestFramework\Annotation\AdminConfigFixture()
        );
    }
}
