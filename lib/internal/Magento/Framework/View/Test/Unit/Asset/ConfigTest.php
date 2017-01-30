<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
}
