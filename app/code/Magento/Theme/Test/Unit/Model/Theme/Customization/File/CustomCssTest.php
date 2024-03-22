<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme\Customization\File;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Design\Theme\Customization\Path;
use Magento\Framework\View\Design\Theme\FileFactory;
use Magento\Framework\View\Design\Theme\FileInterface;
use Magento\Theme\Model\Theme\Customization\File\CustomCss;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomCssTest extends TestCase
{
    /**
     * @var MockObject|Path
     */
    protected $customizationPath;

    /**
     * @var MockObject|FileFactory
     */
    protected $fileFactory;

    /**
     * @var MockObject|Filesystem
     */
    protected $filesystem;

    /**
     * @var CustomCss
     */
    protected $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customizationPath = $this->getMockBuilder(Path::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactory = $this->getMockBuilder(FileFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new CustomCss(
            $this->customizationPath,
            $this->fileFactory,
            $this->filesystem
        );
    }

    /**
     * @return void
     * cover _prepareSortOrder
     * cover _prepareFileName
     */
    public function testPrepareFile(): void
    {
        $file = $this->getMockBuilder(FileInterface::class)
            ->onlyMethods(
                [
                    'delete',
                    'save',
                    'getContent',
                    'getFileInfo',
                    'getFullPath',
                    'getFileName',
                    'setFileName',
                    'getTheme',
                    'setTheme',
                    'getCustomizationService',
                    'setCustomizationService'
                ]
            )
            ->addMethods(['getId', 'setData'])
            ->getMockForAbstractClass();
        $file->expects($this->any())
            ->method('setData')
            ->willReturnMap(
                [
                    ['file_type', CustomCss::TYPE, $this->returnSelf()],
                    ['file_path', CustomCss::TYPE . '/' . CustomCss::FILE_NAME, $this->returnSelf()],
                    ['sort_order', CustomCss::SORT_ORDER, $this->returnSelf()]
                ]
            );
        $file->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $file
            ->method('getFileName')
            ->willReturnOnConsecutiveCalls(null, CustomCss::FILE_NAME);
        $file->expects($this->once())
            ->method('setFileName')
            ->with(CustomCss::FILE_NAME);

        /** @var FileInterface $file */
        $this->assertInstanceOf(
            CustomCss::class,
            $this->object->prepareFile($file)
        );
    }
}
