<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Plugin;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseUrlConfigPluginTest extends TestCase
{
    /**
     * @var SubscriptionUpdateHandler|MockObject
     */
    private $subscriptionUpdateHandlerMock;

    /**
     * @var Value|MockObject
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
    protected function setUp(): void
    {
        $this->subscriptionUpdateHandlerMock = $this->createMock(SubscriptionUpdateHandler::class);
        $this->configValueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPath', 'getScope'])
            ->onlyMethods(['isValueChanged', 'getOldValue'])
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
    public static function afterSavePluginIsNotApplicableDataProvider()
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
