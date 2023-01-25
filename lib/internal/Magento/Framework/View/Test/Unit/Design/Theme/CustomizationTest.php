<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test of theme customization model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\View\Design\Theme\Customization;
use Magento\Framework\View\Design\Theme\Customization\Path;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\Theme\FileProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomizationTest extends TestCase
{
    /**
     * @var Customization
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $fileProvider;

    /**
     * @var MockObject
     */
    protected $customizationPath;

    /**
     * @var MockObject
     */
    protected $theme;

    protected function setUp(): void
    {
        $this->fileProvider = $this->getMockForAbstractClass(FileProviderInterface::class);
        $collectionFactory = $this->createPartialMock(
            \Magento\Theme\Model\ResourceModel\Theme\File\CollectionFactory::class,
            ['create']
        );
        $collectionFactory->expects($this->any())->method('create')->willReturn($this->fileProvider);
        $this->customizationPath = $this->createMock(Path::class);
        $this->theme = $this->createPartialMock(Theme::class, ['__wakeup', 'save', 'load']);

        $this->model = new Customization($this->fileProvider, $this->customizationPath, $this->theme);
    }

    protected function tearDown(): void
    {
        $this->model = null;
        $this->fileProvider = null;
        $this->customizationPath = null;
        $this->theme = null;
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getFiles
     * @covers \Magento\Framework\View\Design\Theme\Customization::__construct
     */
    public function testGetFiles()
    {
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme
        )->willReturn(
            []
        );
        $this->assertEquals([], $this->model->getFiles());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getFilesByType
     */
    public function testGetFilesByType()
    {
        $type = 'sample-type';
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme,
            ['file_type' => $type]
        )->willReturn(
            []
        );
        $this->assertEquals([], $this->model->getFilesByType($type));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::generateFileInfo
     */
    public function testGenerationOfFileInfo()
    {
        $file = $this->createPartialMock(File::class, ['__wakeup', 'getFileInfo']);
        $file->expects($this->once())->method('getFileInfo')->willReturn(['sample-generation']);
        $this->assertEquals([['sample-generation']], $this->model->generateFileInfo([$file]));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getCustomizationPath
     */
    public function testGetCustomizationPath()
    {
        $this->customizationPath->expects(
            $this->once()
        )->method(
            'getCustomizationPath'
        )->with(
            $this->theme
        )->willReturn(
            'path'
        );
        $this->assertEquals('path', $this->model->getCustomizationPath());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getThemeFilesPath
     * @dataProvider getThemeFilesPathDataProvider
     * @param string $type
     * @param string $expectedMethod
     */
    public function testGetThemeFilesPath($type, $expectedMethod)
    {
        $this->theme->setData(['id' => 123, 'type' => $type, 'area' => 'area51', 'theme_path' => 'theme_path']);
        $this->customizationPath->expects(
            $this->once()
        )->method(
            $expectedMethod
        )->with(
            $this->theme
        )->willReturn(
            'path'
        );
        $this->assertEquals('path', $this->model->getThemeFilesPath());
    }

    /**
     * @return array
     */
    public function getThemeFilesPathDataProvider()
    {
        return [
            'physical' => [ThemeInterface::TYPE_PHYSICAL, 'getThemeFilesPath'],
            'virtual' => [ThemeInterface::TYPE_VIRTUAL, 'getCustomizationPath'],
            'staging' => [ThemeInterface::TYPE_STAGING, 'getCustomizationPath']
        ];
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPath()
    {
        $this->customizationPath->expects(
            $this->once()
        )->method(
            'getCustomViewConfigPath'
        )->with(
            $this->theme
        )->willReturn(
            'path'
        );
        $this->assertEquals('path', $this->model->getCustomViewConfigPath());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::reorder
     * @dataProvider customFileContent
     */
    public function testReorder($sequence, $filesContent)
    {
        $files = [];
        $type = 'sample-type';
        foreach ($filesContent as $fileContent) {
            $file = $this->createPartialMock(File::class, ['__wakeup', 'save']);
            $file->expects($fileContent['isCalled'])->method('save')->willReturnSelf();
            $file->setData($fileContent['content']);
            $files[] = $file;
        }
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme,
            ['file_type' => $type]
        )->willReturn(
            $files
        );
        $this->assertInstanceOf(
            CustomizationInterface::class,
            $this->model->reorder($type, $sequence)
        );
    }

    /**
     * Reorder test content
     *
     * @return array
     */
    public function customFileContent()
    {
        return [
            [
                'sequence' => [3, 2, 1],
                'filesContent' => [
                    [
                        'isCalled' => $this->once(),
                        'content' => [
                            'id' => 1,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file1.css',
                            'content' => 'css content',
                            'sort_order' => 1,
                        ],
                    ],
                    [
                        'isCalled' => $this->never(),
                        'content' => [
                            'id' => 2,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file2.css',
                            'content' => 'css content',
                            'sort_order' => 1,
                        ]
                    ],
                    [
                        'isCalled' => $this->once(),
                        'content' => [
                            'id' => 3,
                            'theme_id' => 123,
                            'file_path' => 'css/custom_file3.css',
                            'content' => 'css content',
                            'sort_order' => 5,
                        ]
                    ],
                ],
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Customization::delete
     */
    public function testDelete()
    {
        $file = $this->createPartialMock(File::class, ['__wakeup', 'delete']);
        $file->expects($this->once())->method('delete')->willReturnSelf();
        $file->setData(
            [
                'id' => 1,
                'theme_id' => 123,
                'file_path' => 'css/custom_file1.css',
                'content' => 'css content',
                'sort_order' => 1,
            ]
        );
        $this->fileProvider->expects(
            $this->once()
        )->method(
            'getItems'
        )->with(
            $this->theme
        )->willReturn(
            [$file]
        );

        $this->assertInstanceOf(
            CustomizationInterface::class,
            $this->model->delete([1])
        );
    }
}
