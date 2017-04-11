<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Subscription as SubscriptionModel;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\SearchInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueFactoryMock;

    /**
     * @var Value|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueMock;

    /**
     * @var SearchInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configStructureMock;

    /**
     * @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueResourceMock;

    /**
     * @var Field|\PHPUnit_Framework_MockObject_MockObject
     */
    private $elementFieldMock;

    /**
     * @var ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var SubscriptionHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionHandlerMock;

    /**
     * @var SubscriptionStatusProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusProviderMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SubscriptionModel
     */
    private $subscriptionModel;

    /**
     * @var string
     */
    private $configPath = 'configPath';

    /**
     * @var string
     */
    private $enableConfigStructurePath = 'enabledConfigStructurePath';

    /**
     * @var string
     */
    private $yesValueDropdown = 1;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configValueFactoryMock = $this->getMockBuilder(ValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configValueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValue', 'setPath'])
            ->getMock();

        $this->configStructureMock = $this->getMockBuilder(SearchInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configValueResourceMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->elementFieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reinitableConfigMock = $this->getMockBuilder(ReinitableConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriptionHandlerMock = $this->getMockBuilder(SubscriptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->statusProviderMock = $this->getMockBuilder(SubscriptionStatusProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->subscriptionModel = $this->objectManagerHelper->getObject(
            SubscriptionModel::class,
            [
                'configValueFactory' => $this->configValueFactoryMock,
                'configStructure' => $this->configStructureMock,
                'configValueResource' => $this->configValueResourceMock,
                'reinitableConfig' => $this->reinitableConfigMock,
                'enabledConfigStructurePath' => $this->enableConfigStructurePath,
                'yesValueDropdown'  => $this->yesValueDropdown,
                'subscriptionHandler' => $this->subscriptionHandlerMock,
                'statusProvider' => $this->statusProviderMock,
            ]
        );
    }

    /**
     * @dataProvider enabledDataProvider
     *
     * @param boolean $backendModel
     * @param string $configPath
     *
     * @return void
     */
    public function testEnabled($backendModel, $configPath)
    {
        $this->configStructureMock
            ->expects($this->once())
            ->method('getElement')
            ->with($this->enableConfigStructurePath)
            ->willReturn($this->elementFieldMock);
        $this->elementFieldMock
            ->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn($backendModel);
        if ($backendModel) {
            $this->elementFieldMock
                ->expects($this->once())
                ->method('getBackendModel')
                ->willReturn($this->configValueMock);
        } else {
            $this->configValueFactoryMock
                ->expects($this->once())
                ->method('create')
                ->willReturn($this->configValueMock);
        }
        $this->elementFieldMock
            ->expects($this->once())
            ->method('getConfigPath')
            ->willReturn($configPath);
        $configPath = $configPath ?: $this->enableConfigStructurePath;
        $this->configValueResourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->configValueMock, $configPath, 'path')
            ->willReturnSelf();
        $this->configValueMock
            ->expects($this->once())
            ->method('setValue')
            ->with(1)
            ->willReturnSelf();
        $this->configValueMock
            ->expects($this->once())
            ->method('setPath')
            ->with($configPath)
            ->willReturnSelf();
        $this->configValueResourceMock
            ->expects($this->once())
            ->method('save')
            ->with($this->configValueMock)
            ->willReturnSelf();
        $this->reinitableConfigMock
            ->expects($this->once())
            ->method('reinit')
            ->willReturnSelf();
        $this->assertTrue($this->subscriptionModel->enable());
    }

    /**
     * @return array
     */
    public function enabledDataProvider()
    {
        return [
            'TestWithBackendModelWithoutConfigPath' => [true, null],
            'TestWithBackendModelWithConfigPath' => [true, $this->configPath],
            'TestWithoutBackendModelWithoutConfigPath' => [false, null],
            'TestWithoutBackendModelWithConfigPath' => [false, $this->configPath],
        ];
    }

    /**
     * @dataProvider retryDataProvider
     * @param string $status
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $invokedCount
     * @return void
     */
    public function testRetry($status, \PHPUnit_Framework_MockObject_Matcher_InvokedCount $invokedCount)
    {
        $this->statusProviderMock
            ->expects($this->once())
            ->method('getStatus')
            ->with()
            ->willReturn($status);
        $this->subscriptionHandlerMock
            ->expects(clone $invokedCount)
            ->method('processEnabled')
            ->with()
            ->willReturn(true);
        $this->reinitableConfigMock
            ->expects(clone $invokedCount)
            ->method('reinit')
            ->with()
            ->willReturnSelf();
        $this->assertTrue($this->subscriptionModel->retry());
    }

    /**
     * @return array
     */
    public function retryDataProvider()
    {
        return [
            'Status Pending' => [SubscriptionStatusProvider::PENDING, $this->never()],
            'Status Failed' => [SubscriptionStatusProvider::FAILED, $this->once()],
            'Status Disabled' => [SubscriptionStatusProvider::DISABLED, $this->never()],
        ];
    }
}
