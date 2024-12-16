<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\Minification;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Framework\View\Asset\Minification
 */
class MinificationTest extends TestCase
{
    /**
     * @var Minification
     */
    protected $minification;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var State|MockObject
     */
    protected $appStateMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->appStateMock = $this->getMockBuilder(State::class)
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
    public static function isEnabledDataProvider()
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
    public static function addMinifiedSignDataProvider()
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
    public static function removeMinifiedSignDataProvider()
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
    public static function isMinifiedFilenameDataProvider()
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
     * Test dev/js/minify_exclude system value as array
     *
     * @return void
     */
    public function testGetExcludes()
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('dev/js/minify_exclude')
            ->willReturn([
                'tiny_mce' => '/tiny_mce/',
                'some_other_unique_name' => '/tiny_mce2/'
            ]);

        $expected = ['/tiny_mce/', '/tiny_mce2/'];
        $this->assertEquals($expected, $this->minification->getExcludes('js'));
        /** check cache: */
        $this->assertEquals($expected, $this->minification->getExcludes('js'));
    }

    /**
     * Test dev/js/minify_exclude system value backward compatibility when value was a string
     *
     * @param string $value
     * @param array $expectedValue
     * @return void
     *
     * @dataProvider getExcludesTinyMceAsStringDataProvider
     */
    public function testGetExcludesTinyMceAsString(string $value, array $expectedValue)
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('dev/js/minify_exclude')
            ->willReturn($value);

        $this->assertEquals($expectedValue, $this->minification->getExcludes('js'));
        /** check cache: */
        $this->assertEquals($expectedValue, $this->minification->getExcludes('js'));
    }

    /**
     * @return array
     */
    public static function getExcludesTinyMceAsStringDataProvider()
    {
        return [
            ["/tiny_mce/  \n  /tiny_mce2/", ['/tiny_mce/', '/tiny_mce2/']],
            ['/tiny_mce/', ['/tiny_mce/']],
            [' /tiny_mce/', ['/tiny_mce/']],
        ];
    }
}
