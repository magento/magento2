<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Plugin;

use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Config\Model\Config\Backend\Baseurl;
use Magento\Framework\FlagManager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;

/**
 * Class BaseUrlConfigPluginTest
 */
class BaseUrlConfigPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlagManager | \PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var BaseUrl | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueMock;

    /**
     * @var SubscriptionStatusProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionStatusProvider;

    /**
     * @var WriterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

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
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configValueMock = $this->getMockBuilder(Baseurl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriptionStatusProvider = $this->getMockBuilder(SubscriptionStatusProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            BaseUrlConfigPlugin::class,
            [
                'flagManager' => $this->flagManagerMock,
                'subscriptionStatusProvider' => $this->subscriptionStatusProvider,
                'configWriter' => $this->configWriterMock
            ]
        );
    }

    /**
     * @param array $testData
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $saveConfigInvokeMatcher
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $oldValueInvokeMatcher
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $saveFlagInvokeMatcher
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $configValueGetPathMatcher
     *
     * @return void
     * @dataProvider pluginDataProvider
     */
    public function testPluginForAfterSave(
        array $testData,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $saveConfigInvokeMatcher,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $oldValueInvokeMatcher,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $saveFlagInvokeMatcher,
        \PHPUnit_Framework_MockObject_Matcher_InvokedCount $configValueGetPathMatcher
    ) {
        $this->configValueMock->expects($this->once())
            ->method('isValueChanged')
            ->willReturn($testData['isValueChanged']);

        $this->configValueMock->expects($configValueGetPathMatcher)
            ->method('getData')
            ->with('path')
            ->willReturn($testData['path']);
        $this->subscriptionStatusProvider->expects($this->any())->method('getStatus')
            ->willReturn($testData['subscriptionStatus']);

        $oldUrl = 'mage.dev';
        $this->configValueMock->expects($oldValueInvokeMatcher)
            ->method('getOldValue')
            ->willReturn($oldUrl);
        $this->flagManagerMock->expects($saveFlagInvokeMatcher)
            ->method('saveFlag')
            ->with(BaseUrlConfigPlugin::OLD_BASE_URL_FLAG_CODE, $oldUrl);

        $this->configWriterMock->expects($saveConfigInvokeMatcher)->method('save')
            ->with(
                BaseUrlConfigPlugin::UPDATE_CRON_STRING_PATH,
                '0 * * * *'
            );

        $this->assertEquals(
            $this->configValueMock,
            $this->plugin->afterAfterSave($this->configValueMock, $this->configValueMock)
        );
    }

    /**
     * @return array
     */
    public function pluginDataProvider()
    {
        return [
            'setup_subscription_update_cron_job' => [
                'testData' => [
                    'isValueChanged' => true,
                    'subscriptionStatus' => SubscriptionStatusProvider::ENABLED,
                    'path' => Store::XML_PATH_SECURE_BASE_URL
                ],
                'saveConfigInvokeMatcher' => $this->once(),
                'oldValueInvokeMatcher' => $this->once(),
                'saveFlagInvokeMatcher' => $this->once(),
                'configValueGetPathMatcher' => $this->once(),
            ],
            'base_url_not_changed' => [
                'testData' => [
                    'isValueChanged' => false,
                    'subscriptionStatus' => SubscriptionStatusProvider::ENABLED,
                    'path' => Store::XML_PATH_SECURE_BASE_URL
                ],
                'saveConfigInvokeMatcher' => $this->never(),
                'oldValueInvokeMatcher' => $this->never(),
                'saveFlagInvokeMatcher' => $this->never(),
                'configValueGetPathMatcher' => $this->never(),
            ],
            'analytics_disabled' => [
                'testData' => [
                    'isValueChanged' => true,
                    'subscriptionStatus' => SubscriptionStatusProvider::DISABLED,
                    'path' => Store::XML_PATH_SECURE_BASE_URL
                ],
                'saveConfigInvokeMatcher' => $this->never(),
                'oldValueInvokeMatcher' => $this->never(),
                'saveFlagInvokeMatcher' => $this->never(),
                'configValueGetPathMatcher' => $this->once(),
            ],
            'analytics_pending' => [
                'testData' => [
                    'isValueChanged' => true,
                    'subscriptionStatus' => SubscriptionStatusProvider::PENDING,
                    'path' => Store::XML_PATH_SECURE_BASE_URL
                ],
                'saveConfigInvokeMatcher' => $this->never(),
                'oldValueInvokeMatcher' => $this->never(),
                'saveFlagInvokeMatcher' => $this->never(),
                'configValueGetPathMatcher' => $this->once(),
            ],
            'unsecure_url_changed' => [
                'testData' => [
                    'isValueChanged' => true,
                    'subscriptionStatus' => SubscriptionStatusProvider::PENDING,
                    'path' => Store::XML_PATH_UNSECURE_BASE_URL
                ],
                'saveConfigInvokeMatcher' => $this->never(),
                'oldValueInvokeMatcher' => $this->never(),
                'saveFlagInvokeMatcher' => $this->never(),
                'configValueGetPathMatcher' => $this->once(),
            ]
        ];
    }
}
