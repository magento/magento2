<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;


use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Analytics\Model\Subscription as SubscriptionModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConfigMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SubscriptionModel
     */
    private $subscriptionModel;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resourceConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->subscriptionModel = $this->objectManagerHelper->getObject(
            SubscriptionModel::class,
            [
                'resourceConfig' => $this->resourceConfigMock,
                'enabledConfigPath' => 'test/config/path',
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecuteSubscriptionEnabled()
    {
        $this->resourceConfigMock->expects($this->once())
            ->method('saveConfig')
            ->with(
                'test/config/path',
                1,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                Store::DEFAULT_STORE_ID
            )
            ->willReturn(true);

        $this->subscriptionModel->enable();
    }
}
