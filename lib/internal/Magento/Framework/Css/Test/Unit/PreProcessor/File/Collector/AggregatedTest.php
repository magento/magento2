<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Css\Test\Unit\PreProcessor\File\Collector;

use Magento\Framework\Css\PreProcessor\File\Collector\Aggregated;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\FileList;
use Magento\Framework\View\File\FileList\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests Aggregate
 */
class AggregatedTest extends TestCase
{
    /**
     * @var Factory|MockObject
     */
    protected $fileListFactoryMock;

    /**
     * @var FileList|MockObject
     */
    protected $fileListMock;

    /**
     * @var CollectorInterface|MockObject
     */
    protected $libraryFilesMock;

    /**
     * @var CollectorInterface|MockObject
     */
    protected $baseFilesMock;

    /**
     * @var CollectorInterface|MockObject
     */
    protected $overriddenBaseFilesMock;

    /**
     * @var ThemeInterface|MockObject
     */
    protected $themeMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * Setup tests
     * @return void
     */
    public function setup(): void
    {
        $this->fileListFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()->getMock();
        $this->fileListMock = $this->getMockBuilder(FileList::class)
            ->disableOriginalConstructor()->getMock();
        $this->fileListFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($this->fileListMock));
        $this->libraryFilesMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->baseFilesMock = $this->getMockBuilder(CollectorInterface::class)->getMock();
        $this->overriddenBaseFilesMock = $this->getMockBuilder(CollectorInterface::class)
            ->getMock();
        $this->themeMock = $this->getMockBuilder(ThemeInterface::class)->getMock();
    }

    public function testGetFilesEmpty()
    {
        $this->libraryFilesMock->expects($this->any())->method('getFiles')->will($this->returnValue([]));
        $this->baseFilesMock->expects($this->any())->method('getFiles')->will($this->returnValue([]));
        $this->overriddenBaseFilesMock->expects($this->any())->method('getFiles')->will($this->returnValue([]));

        $aggregated = new Aggregated(
            $this->fileListFactoryMock,
            $this->libraryFilesMock,
            $this->baseFilesMock,
            $this->overriddenBaseFilesMock,
            $this->loggerMock
        );

        $this->themeMock->expects($this->any())->method('getInheritedThemes')->will($this->returnValue([]));
        $this->themeMock->expects($this->any())->method('getCode')->will($this->returnValue('theme_code'));

        $this->loggerMock->expects($this->once())
            ->method('notice')
            ->with('magento_import returns empty result by path * for theme theme_code', []);

        $aggregated->getFiles($this->themeMock, '*');
    }

    /**
     *
     * @dataProvider getFilesDataProvider
     *
     * @param array $libraryFiles Files in lib directory
     * @param array $baseFiles Files in base directory
     * @param array $themeFiles Files in theme
     * *
     * @return void
     */
    public function testGetFiles($libraryFiles, $baseFiles, $themeFiles)
    {
        $this->fileListMock->expects($this->at(0))->method('add')->with($this->equalTo($libraryFiles));
        $this->fileListMock->expects($this->at(1))->method('add')->with($this->equalTo($baseFiles));
        $this->fileListMock->expects($this->any())->method('getAll')->will($this->returnValue(['returnedFile']));

        $subPath = '*';
        $this->libraryFilesMock->expects($this->atLeastOnce())
            ->method('getFiles')
            ->with($this->themeMock, $subPath)
            ->will($this->returnValue($libraryFiles));

        $this->baseFilesMock->expects($this->atLeastOnce())
            ->method('getFiles')
            ->with($this->themeMock, $subPath)
            ->will($this->returnValue($baseFiles));

        $this->overriddenBaseFilesMock->expects($this->any())
            ->method('getFiles')
            ->will($this->returnValue($themeFiles));

        $aggregated = new Aggregated(
            $this->fileListFactoryMock,
            $this->libraryFilesMock,
            $this->baseFilesMock,
            $this->overriddenBaseFilesMock,
            $this->loggerMock
        );

        $inheritedThemeMock = $this->getMockBuilder(ThemeInterface::class)->getMock();
        $this->themeMock->expects($this->any())->method('getInheritedThemes')
            ->will($this->returnValue([$inheritedThemeMock]));

        $this->assertEquals(['returnedFile'], $aggregated->getFiles($this->themeMock, $subPath));
    }

    /**
     * Provides test data for testGetFiles()
     *
     * @return array
     */
    public function getFilesDataProvider()
    {
        return [
            'all files' => [['file1'], ['file2'], ['file3']],
            'no library' => [[], ['file1', 'file2'], ['file3']],
        ];
    }
}
