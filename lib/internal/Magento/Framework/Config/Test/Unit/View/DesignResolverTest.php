<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\View;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Config\View\DesignResolver;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\ThemeInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\DesignInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class DesignResolverTest extends TestCase
{
    /**
     * @var DesignResolver
     */
    private $designResolver;

    /**
     * @var ModuleReader|Mock
     */
    private $moduleReaderMock;

    /**
     * @var FileIteratorFactory|Mock
     */
    private $iteratorFactoryMock;

    /**
     * @var DesignInterface|Mock
     */
    private $designMock;

    /**
     * @var Filesystem|Mock
     */
    private $filesystemMock;

    /**
     * @var ResolverInterface|Mock
     */
    private $resolverMock;

    /**
     * @var FileIterator|Mock
     */
    private $fileIteratorMock;

    /**
     * @var ThemeInterface|Mock
     */
    private $currentThemeMock;

    /**
     * @var CustomizationInterface|Mock
     */
    private $customizationMock;

    /**
     * @var Filesystem\Directory\ReadInterface|Mock
     */
    private $directoryReadMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->moduleReaderMock = $this->createMock(ModuleReader::class);
        $this->iteratorFactoryMock = $this->createMock(FileIteratorFactory::class);
        $this->designMock = $this->getMockForAbstractClass(DesignInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->resolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->fileIteratorMock = $this->createMock(FileIterator::class);
        $this->currentThemeMock = $this->getMockBuilder(ThemeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomization'])
            ->getMockForAbstractClass();

        $this->customizationMock = $this->getMockForAbstractClass(CustomizationInterface::class);
        $this->directoryReadMock = $this->getMockForAbstractClass(Filesystem\Directory\ReadInterface::class);

        $this->iteratorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->fileIteratorMock);
        $this->designMock->expects($this->any())
            ->method('getDesignTheme')
            ->willReturn($this->currentThemeMock);
        $this->designMock->expects($this->any())
            ->method('getArea')
            ->willReturn('default');

        $this->designResolver = new DesignResolver(
            $this->moduleReaderMock,
            $this->iteratorFactoryMock,
            $this->designMock,
            $this->filesystemMock,
            $this->resolverMock
        );
    }

    public function testGetWithThemeConfig()
    {
        $filename = 'test.xml';
        $iterator = [];
        $themeConfigFile = 'theme.xml';
        $scope = 'global';

        $this->moduleReaderMock->expects($this->once())
            ->method('getConfigurationFiles')
            ->with($filename)
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->expects($this->once())
            ->method('toArray')
            ->willReturn($iterator);
        $this->currentThemeMock->expects($this->once())
            ->method('getCustomization')
            ->willReturn($this->customizationMock);
        $this->customizationMock->expects($this->once())
            ->method('getCustomViewConfigPath')
            ->willReturn($themeConfigFile);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->directoryReadMock);
        $this->directoryReadMock->expects($this->exactly(3))
            ->method('getRelativePath')
            ->with($themeConfigFile)
            ->willReturn($themeConfigFile);
        $this->directoryReadMock->expects($this->once())
            ->method('isExist')
            ->with($themeConfigFile)
            ->willReturn(true);
        $this->directoryReadMock->expects($this->once())
            ->method('readFile')
            ->with($themeConfigFile)
            ->willReturn('some_theme_data');

        $this->assertSame(['theme.xml' => 'some_theme_data'], $this->designResolver->get($filename, $scope));
    }

    public function testGetMissedThemeConfig()
    {
        $filename = 'test.xml';
        $iterator = [];
        $themeConfigFile = '';
        $scope = 'global';

        $this->moduleReaderMock->expects($this->once())
            ->method('getConfigurationFiles')
            ->with($filename)
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->expects($this->once())
            ->method('toArray')
            ->willReturn($iterator);
        $this->currentThemeMock->expects($this->once())
            ->method('getCustomization')
            ->willReturn($this->customizationMock);
        $this->customizationMock->expects($this->once())
            ->method('getCustomViewConfigPath')
            ->willReturn($themeConfigFile);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->directoryReadMock);
        $this->directoryReadMock->expects($this->never())
            ->method('getRelativePath');
        $this->directoryReadMock->expects($this->never())
            ->method('isExist');
        $this->resolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn(__DIR__ . '/_files/design.xml');

        $this->assertArrayHasKey(__DIR__ . '/_files/design.xml', $this->designResolver->get($filename, $scope));
    }

    public function testGetDefault()
    {
        $this->assertInstanceOf(
            FileIterator::class,
            $this->designResolver->get('default.xml', 'default')
        );
    }

    public function testGetParentsDefault()
    {
        $this->assertInstanceOf(
            FileIterator::class,
            $this->designResolver->get('default.xml', 'default')
        );
    }

    public function testGetParents()
    {
        $filename = 'test.xml';
        $iterator = [];
        $scope = 'global';

        $this->moduleReaderMock->expects($this->once())
            ->method('getConfigurationFiles')
            ->with($filename)
            ->willReturn($this->fileIteratorMock);
        $this->fileIteratorMock->expects($this->once())
            ->method('toArray')
            ->willReturn($iterator);
        $this->resolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn(__DIR__ . '/_files/design2.xml');

        $this->assertSame(
            $iterator,
            $this->designResolver->getParents($filename, $scope)
        );
    }
}
