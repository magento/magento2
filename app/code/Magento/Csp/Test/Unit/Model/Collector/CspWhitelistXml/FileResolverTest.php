<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\Collector\CspWhitelistXml;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\ThemeInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Csp\Model\Collector\CspWhitelistXml\FileResolver;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\Config\CompositeFileIteratorFactory;
use Magento\Framework\View\Design\Theme\CustomizationInterfaceFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileResolverTest extends TestCase
{
    /**
     * @var FileResolver
     */
    private $model;

    /**
     * @var FileResolverInterface
     */
    private $moduleFileResolverMock;

    /**
     * @var DesignInterface
     */
    private $designMock;

    /**
     * @var CustomizationInterfaceFactory
     */
    private $customizationFactoryMock;

    /**
     * @var Filesystem
     */
    private $filesystemMock;

    /**
     * @var CompositeFileIteratorFactory
     */
    private $iteratorFactoryMock;

    /**
     * @var ReadInterface
     */
    private $readInterfaceMock;

    /**
     * @var ThemeInterface
     */
    private $themeInterFaceMock;

    /**
     * @var CustomizationInterface
     */
    private $customizationInterfaceMock;

    protected function setUp(): void
    {
        $this->moduleFileResolverMock = $this->getMockBuilder(FileResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeInterFaceMock = $this->getMockBuilder(ThemeInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->designMock->expects($this->once())
            ->method('getDesignTheme')
            ->willReturn($this->themeInterFaceMock);

        $this->customizationFactoryMock = $this->getMockBuilder(CustomizationInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->customizationInterfaceMock = $this->getMockBuilder(CustomizationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);

        $this->readInterfaceMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->readInterfaceMock);

        $this->iteratorFactoryMock = $this->getMockBuilder(CompositeFileIteratorFactory::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->model = new FileResolver(
            $this->moduleFileResolverMock,
            $this->designMock,
            $this->customizationFactoryMock,
            $this->filesystemMock,
            $this->iteratorFactoryMock
        );
    }

    /**
     * Test for get method with frontend scope.
     *
     * @param string $scope
     * @param string $fileName
     * @param array $fileList
     * @param string $themeFilesPath
     *
     * @return void
     * @dataProvider providerGetFrontend
     */
    public function testGetFrontend(string $scope, string $fileName, array $fileList, string $themeFilesPath): void
    {
        $this->moduleFileResolverMock->expects($this->once())
            ->method('get')
            ->with($fileName, $scope)
            ->willReturn($fileList);

        $this->customizationFactoryMock->expects($this->any())
            ->method('create')
            ->with(['theme' => $this->themeInterFaceMock])
            ->willReturn($this->customizationInterfaceMock);

        $this->customizationInterfaceMock->expects($this->once())
            ->method('getThemeFilesPath')
            ->willReturn($themeFilesPath);

        $this->readInterfaceMock->expects($this->once())
            ->method('isExist')
            ->with($themeFilesPath.'/etc/'.$fileName)
            ->willReturn(true);

        $this->iteratorFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'paths' => array_reverse([$themeFilesPath.'/etc/'.$fileName]),
                    'existingIterator' => $fileList
                ]
            )
            ->willReturn($fileList);

        $this->assertEquals($fileList, $this->model->get($fileName, $scope));
    }

    /**
     * Test for get method with global scope.
     *
     * @param string $scope
     * @param string $fileName
     * @param array $fileList
     *
     * @return void
     * @dataProvider providerGetGlobal
     */
    public function testGetGlobal(string $scope, string $fileName, array $fileList): void
    {
        $this->moduleFileResolverMock->expects($this->once())
            ->method('get')
            ->with($fileName, $scope)
            ->willReturn($fileList);
        $this->assertEquals($fileList, $this->model->get($fileName, $scope));
    }

    /**
     * Data provider for get global scope tests.
     *
     * @return array
     */
    public static function providerGetGlobal(): array
    {
        return [
            [
                'global',
                'csp_whitelist.xml',
                ['anyvendor/anymodule/etc/csp_whitelist.xml']
            ]
        ];
    }

    /**
     * Data provider for get frontend & adminhtml scope tests.
     *
     * @return array
     */
    public static function providerGetFrontend(): array
    {
        return [
            [
                'frontend',
                'csp_whitelist.xml',
                ['themevendor/theme/etc/csp_whitelist.xml'],
                'themevendor/theme'
            ],
            [
                'adminhtml',
                'csp_whitelist.xml',
                ['adminthemevendor/admintheme/etc/csp_whitelist.xml'],
                'adminthemevendor/admintheme'
            ]
        ];
    }
}
