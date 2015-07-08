<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\State;
use Magento\Framework\View\Asset\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Tests Magento\Framework\View\Asset\Config
 */
class ConfigTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\State
     */
    private $appStateMock;

    /**
     * @var \Magento\Framework\View\Asset\Config
     */
    private $model;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();
        $this->appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Config($this->scopeConfigMock, $this->appStateMock);
    }

    /**
     * @param bool $booleanData
     * @dataProvider booleanDataProvider
     * @return void
     */
    public function testIsMergeCssFiles($booleanData)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_MERGE_CSS_FILES, ScopeInterface::SCOPE_STORE)
            ->willReturn($booleanData);
        $this->assertSame($booleanData, $this->model->isMergeCssFiles());
    }

    /**
     * @param bool $booleanData
     * @dataProvider booleanDataProvider
     * @return void
     */
    public function testIsMergeJsFiles($booleanData)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_MERGE_JS_FILES, ScopeInterface::SCOPE_STORE)
            ->willReturn($booleanData);
        $this->assertSame($booleanData, $this->model->isMergeJsFiles());
    }

    /**
     * @param bool $configFlag
     * @param string $appMode
     * @param bool $result
     * @dataProvider isAssetMinificationDataProvider
     * @return void
     */
    public function testIsAssetMinification($configFlag, $appMode, $result)
    {
        $contentType = 'content type';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('isSetFlag')
            ->with(
                sprintf(Config::XML_PATH_MINIFICATION_ENABLED, $contentType),
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($configFlag);
        $this->appStateMock
            ->expects($this->any())
            ->method('getMode')
            ->willReturn($appMode);

        $this->assertEquals($result, $this->model->isAssetMinification($contentType));
    }

    /**
     * @return array
     */
    public function isAssetMinificationDataProvider()
    {
        return [
            [false, State::MODE_DEFAULT, false],
            [false, State::MODE_PRODUCTION, false],
            [false, State::MODE_DEVELOPER, false],
            [true, State::MODE_DEFAULT, true],
            [true, State::MODE_PRODUCTION, true],
            [true, State::MODE_DEVELOPER, false]
        ];
    }
}
