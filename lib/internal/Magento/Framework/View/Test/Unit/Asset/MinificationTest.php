<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\State;
use Magento\Framework\View\Asset\Minification;
use Magento\Store\Model\ScopeInterface;

/**
 * Unit test for Magento\Framework\View\Asset\Minification
 */
class MinificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\Minification
     */
    protected $minification;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->minification = new Minification(
            $this->scopeConfigMock,
            $this->appStateMock
        );
    }

    /**
     * @return void
     */
    public function testIsEnabled()
    {
    }

    /**
     * @param bool $configFlag
     * @param string $appMode
     * @param bool $result
     * @dataProvider isEnabledDataProvider
     * @return void
     */
    public function testIsAssetMinification($configFlag, $appMode, $result)
    {
        $contentType = 'content type';
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('isSetFlag')
            ->with(
                sprintf(Minification::XML_PATH_MINIFICATION_ENABLED, $contentType),
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($configFlag);
        $this->appStateMock
            ->expects($this->any())
            ->method('getMode')
            ->willReturn($appMode);

        $this->assertEquals($result, $this->minification->isEnabled($contentType));
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
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

    /**
     * @param string $filename
     * @param bool $isEnabled
     * @param string $expected
     * @dataProvider addMinifiedSignDataProvider
     */
    public function testAddMinifiedSign($filename, $isEnabled, $expected)
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('isSetFlag')
            ->willReturn($isEnabled);
        $this->appStateMock
            ->expects($this->any())
            ->method('getMode')
            ->willReturn(State::MODE_DEFAULT);

        $this->assertEquals(
            $expected,
            $this->minification->addMinifiedSign($filename)
        );
    }

    /**
     * @return array
     */
    public function addMinifiedSignDataProvider()
    {
        return [
            ['test.css', true, 'test.min.css'],
            ['test.css', false, 'test.css'],
            ['test.min.css', true, 'test.min.css']
        ];
    }

    /**
     * @param string $filename
     * @param bool $isEnabled
     * @param string $expected
     * @dataProvider removeMinifiedSignDataProvider
     */
    public function testRemoveMinifiedSign($filename, $isEnabled, $expected)
    {
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('isSetFlag')
            ->willReturn($isEnabled);
        $this->appStateMock
            ->expects($this->any())
            ->method('getMode')
            ->willReturn(State::MODE_DEFAULT);

        $this->assertEquals(
            $expected,
            $this->minification->removeMinifiedSign($filename)
        );
    }

    /**
     * @return array
     */
    public function removeMinifiedSignDataProvider()
    {
        return [
            ['test.css', true, 'test.css'],
            ['test.min.css', true, 'test.css'],
            ['test.min.css', false, 'test.min.css']
        ];
    }

    /**
     * @param string $filename
     * @param bool $result
     * @return void
     * @dataProvider isMinifiedFilenameDataProvider
     */
    public function testIsMinifiedFilename($filename, $result)
    {
        $this->assertEquals(
            $result,
            $this->minification->isMinifiedFilename($filename)
        );
    }

    /**
     * @return array
     */
    public function isMinifiedFilenameDataProvider()
    {
        return [
            ['test.min.css', true],
            ['test.mincss', false],
            ['testmin.css', false],
            ['test.css', false],
            ['test.min', false]
        ];
    }

    /**
     * @return void
     */
    public function testGetExcludes()
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('dev/js/minify_exclude')
            ->willReturn(
                "    /tiny_mce/  \n" .
                "  /tiny_mce2/  "
            );

        $expected = ['/tiny_mce/', '/tiny_mce2/'];
        $this->assertEquals($expected, $this->minification->getExcludes('js'));
        /** check cache: */
        $this->assertEquals($expected, $this->minification->getExcludes('js'));
    }
}
