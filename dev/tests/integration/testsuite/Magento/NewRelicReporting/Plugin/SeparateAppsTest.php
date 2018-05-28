<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Plugin;

use Magento\Framework\App\State;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

class SeparateAppsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
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
            ->setMethods(['setAppName'])
            ->getMock();

        $this->objectManager->configure([NewRelicWrapper::class => ['shared' => true]]);
        $this->objectManager->addSharedInstance($newRelicWrapper, NewRelicWrapper::class);

        $newRelicWrapper->expects($this->once())
            ->method('setAppName')
            ->with($this->equalTo('beverly_hills;beverly_hills_90210'));

        $state = $this->objectManager->get(State::class);

        $state->setAreaCode('90210');
    }
}
