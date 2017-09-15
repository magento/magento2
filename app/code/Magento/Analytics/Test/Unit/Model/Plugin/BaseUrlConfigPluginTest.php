<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Plugin;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Class BaseUrlConfigPluginTest
 */
class BaseUrlConfigPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubscriptionUpdateHandler | \PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionUpdateHandlerMock;

    /**
     * @var Value | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var BaseUrlConfigPlugin
     */
    private $plugin;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->subscriptionUpdateHandlerMock = $this->getMockBuilder(SubscriptionUpdateHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configValueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValueChanged', 'getPath', 'getScope', 'getOldValue'])
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            BaseUrlConfigPlugin::class,
            [
                'subscriptionUpdateHandler' => $this->subscriptionUpdateHandlerMock,
            ]
        );
    }

    /**
     * @param array $configValueData
     * @return void
     * @dataProvider afterSavePluginIsNotApplicableDataProvider
     */
    public function testAfterSavePluginIsNotApplicable(
        array $configValueData
    ) {
        $this->configValueMock
            ->method('isValueChanged')
            ->willReturn($configValueData['isValueChanged']);
        $this->configValueMock
            ->method('getPath')
            ->willReturn($configValueData['path']);
        $this->configValueMock
            ->method('getScope')
            ->willReturn($configValueData['scope']);
        $this->subscriptionUpdateHandlerMock
            ->expects($this->never())
            ->method('processUrlUpdate');

        $this->assertEquals(
            $this->configValueMock,
            $this->plugin->afterAfterSave($this->configValueMock, $this->configValueMock)
        );
    }

    /**
     * @return array
     */
    public function afterSavePluginIsNotApplicableDataProvider()
    {
        return [
            'Value has not been changed' => [
                'Config Value Data' => [
                    'isValueChanged' => false,
                    'path' => Store::XML_PATH_SECURE_BASE_URL,
                    'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                ],
            ],
            'Unsecure URL has been changed' => [
                'Config Value Data' => [
                    'isValueChanged' => true,
                    'path' => Store::XML_PATH_UNSECURE_BASE_URL,
                    'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                ],
            ],
            'Secure URL has been changed not in the Default scope' => [
                'Config Value Data' => [
                    'isValueChanged' => true,
                    'path' => Store::XML_PATH_SECURE_BASE_URL,
                    'scope' => ScopeInterface::SCOPE_STORES
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    public function testAfterSavePluginIsApplicable()
    {
        $this->configValueMock
            ->method('isValueChanged')
            ->willReturn(true);
        $this->configValueMock
            ->method('getPath')
            ->willReturn(Store::XML_PATH_SECURE_BASE_URL);
        $this->configValueMock
            ->method('getScope')
            ->willReturn(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->configValueMock
            ->method('getOldValue')
            ->willReturn('http://store.com');
        $this->subscriptionUpdateHandlerMock
            ->expects($this->once())
            ->method('processUrlUpdate')
            ->with('http://store.com');

        $this->assertEquals(
            $this->configValueMock,
            $this->plugin->afterAfterSave($this->configValueMock, $this->configValueMock)
        );
    }
}
