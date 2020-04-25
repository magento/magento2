<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Theme\Model\Theme\ThemePackageInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThemePackageInfoTest extends TestCase
{
    /**
     * @var Read|MockObject
     */
    private $dirRead;

    /**
     * @var ThemePackageInfo
     */
    private $themePackageInfo;

    /**
     * @var ComponentRegistrar|MockObject
     */
    private $componentRegistrar;

    /**
     * @var ReadFactory|MockObject
     */
    private $dirReadFactory;

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $this->dirRead = $this->createMock(Read::class);
        $this->dirReadFactory = $this->createMock(ReadFactory::class);
        $this->dirReadFactory->expects($this->any())->method('create')->willReturn($this->dirRead);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();
        $this->themePackageInfo = new ThemePackageInfo(
            $this->componentRegistrar,
            $this->dirReadFactory,
            $this->serializerMock
        );
    }

    public function testGetPackageName()
    {
        $themeFileContents = '{"name": "package"}';
        $this->componentRegistrar->expects($this->once())->method('getPath')->willReturn('path/to/A');
        $this->dirRead->expects($this->once())->method('isExist')->with('composer.json')->willReturn(true);
        $this->dirRead->expects($this->once())
            ->method('readFile')
            ->with('composer.json')
            ->willReturn($themeFileContents);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn(json_decode($themeFileContents, true));
        $this->assertEquals('package', $this->themePackageInfo->getPackageName('themeA'));
    }

    public function testGetPackageNameNonExist()
    {
        $this->componentRegistrar->expects($this->once())->method('getPath')->willReturn('path/to/A');
        $this->dirRead->expects($this->once())->method('isExist')->with('composer.json')->willReturn(false);
        $this->dirRead->expects($this->never())->method('readFile')->with('composer.json');
        $this->assertEquals('', $this->themePackageInfo->getPackageName('themeA'));
    }

    public function testGetFullThemePath()
    {
        $themeFileContents = '{"name": "package"}';
        $this->componentRegistrar->expects($this->once())->method('getPaths')->willReturn(['themeA' => 'path/to/A']);
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn($themeFileContents);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn(json_decode($themeFileContents, true));
        $this->assertEquals('themeA', $this->themePackageInfo->getFullThemePath('package'));
        // call one more time to make sure only initialize once
        $this->assertEquals('themeA', $this->themePackageInfo->getFullThemePath('package'));
    }

    public function testGetFullThemePathNonExist()
    {
        $this->componentRegistrar->expects($this->once())->method('getPaths')->willReturn(['themeA' => 'path/to/A']);
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": "package"}');
        $this->assertEquals('', $this->themePackageInfo->getFullThemePath('package-other'));
    }

    public function testGetPackageNameInvalidJson()
    {
        $this->componentRegistrar->expects($this->once())->method('getPath')->willReturn('path/to/A');
        $this->dirRead->expects($this->once())->method('isExist')->willReturn(true);
        $this->dirRead->expects($this->once())->method('readFile')->willReturn('{"name": }');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn(null);
        $this->assertEquals('', $this->themePackageInfo->getPackageName('themeA'));
    }
}
