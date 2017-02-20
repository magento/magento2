<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Plugin\BaseUrlConfigPlugin;

use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configValueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            BaseUrlConfigPlugin::class,
            [
                'flagManager' => $this->flagManagerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testAfterAfterSave()
    {
        $oldUrl = 'mage.dev';
        $this->configValueMock->expects($this->once())
            ->method('isValueChanged')
            ->willReturn(true);
        $this->configValueMock->expects($this->once())
            ->method('getOldValue')
            ->willReturn($oldUrl);
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with('analytics_old_base_url', $oldUrl);
        $this->assertEquals(
            $this->configValueMock,
            $this->plugin->afterAfterSave($this->configValueMock, $this->configValueMock)
        );
    }
}
