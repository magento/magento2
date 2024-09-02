<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Plugin;

use Magento\Framework\App\State;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SeparateAppsTest
 */
class SeparateAppsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoConfigFixture default/newrelicreporting/general/enable 1
     * @magentoConfigFixture default/newrelicreporting/general/app_name beverly_hills
     * @magentoConfigFixture default/newrelicreporting/general/separate_apps 1
     */
    public function testAppNameIsSetWhenConfiguredCorrectly()
    {
        $newRelicWrapper = $this->getMockBuilder(NewRelicWrapper::class)
            ->onlyMethods(['setAppName'])
            ->getMock();

        $this->objectManager->configure([NewRelicWrapper::class => ['shared' => true]]);
        $this->objectManager->addSharedInstance($newRelicWrapper, NewRelicWrapper::class);

        $newRelicWrapper->expects($this->once())
            ->method('setAppName')
            ->with($this->equalTo('beverly_hills;beverly_hills_90210'));

        $state = $this->objectManager->get(State::class);

        $state->setAreaCode('90210');
    }

    /**
     * @magentoConfigFixture default/newrelicreporting/general/enable 1
     * @magentoConfigFixture default/newrelicreporting/general/app_name beverly_hills
     * @magentoConfigFixture default/newrelicreporting/general/separate_apps 0
     */
    public function testAppNameIsNotSetWhenDisabled()
    {
        $newRelicWrapper = $this->getMockBuilder(NewRelicWrapper::class)
            ->onlyMethods(['setAppName'])
            ->getMock();

        $this->objectManager->configure([NewRelicWrapper::class => ['shared' => true]]);
        $this->objectManager->addSharedInstance($newRelicWrapper, NewRelicWrapper::class);

        $newRelicWrapper->expects($this->never())->method('setAppName');

        $state = $this->objectManager->get(State::class);

        $state->setAreaCode('90210');
    }
}
