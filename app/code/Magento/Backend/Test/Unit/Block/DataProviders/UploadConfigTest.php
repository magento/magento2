<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block\DataProviders;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Backend\Block\DataProviders\UploadConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class UploadConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var UploadConfig
     */
    private $uploadConfig;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(ScopeConfig::class)
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->uploadConfig =  $this->objectManagerHelper->getObject(
            UploadConfig::class,
            [
                'config' => $this->config
            ]
        );
    }

    /**
     * @dataProvider configValuesDataProvider()
     * @param int $configValue
     * @param int $expectedValue
     * @return void
     */
    public function testGetIsResizeEnabled(int $configValue, int $expectedValue)
    {
        $this->config->expects($this->once())
            ->method('getValue')
            ->with('system/upload_configuration/enable_resize')
            ->willReturn($configValue);
        $this->assertEquals($expectedValue, $this->uploadConfig->getIsResizeEnabled());
    }

    public function configValuesDataProvider(): array
    {
        return [
            [1, 1],
            [0, 0]
        ];
    }
}
