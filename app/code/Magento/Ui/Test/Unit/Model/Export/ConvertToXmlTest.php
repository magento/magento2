<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Convert\Excel;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToXml;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\SearchResultIterator;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConvertToXmlTest extends TestCase
{
    /**
     * @var ConvertToXml
     */
    protected $model;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var Filter|MockObject
     */
    protected $filter;

    /**
     * @var MetadataProvider|MockObject
     */
    protected $metadataProvider;

    /**
     * @var ExcelFactory|MockObject
     */
    protected $excelFactory;

    /**
     * @var SearchResultIteratorFactory|MockObject
     */
    protected $iteratorFactory;

    /**
     * @var DirectoryWriteInterface|MockObject
     */
    protected $directory;

    /**
     * @var FileWriteInterface|MockObject
     */
    protected $stream;

    /**
     * @var UiComponentInterface|MockObject
     */
    protected $component;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->directory = $this->getMockBuilder(DirectoryWriteInterface::class)
            ->getMockForAbstractClass();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->directory);

        $this->filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataProvider = $this->getMockBuilder(MetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->excelFactory = $this->getMockBuilder(ExcelFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->iteratorFactory = $this->getMockBuilder(SearchResultIteratorFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->component = $this->getMockBuilder(UiComponentInterface::class)
            ->getMockForAbstractClass();

        $this->stream = $this->getMockBuilder(FileWriteInterface::class)
            ->onlyMethods(['lock', 'unlock', 'close'])
            ->getMockForAbstractClass();

        $this->model = new ConvertToXml(
            $this->filesystem,
            $this->filter,
            $this->metadataProvider,
            $this->excelFactory,
            $this->iteratorFactory
        );
    }

    /**
     * @return void
     */
    public function testGetRowData(): void
    {
        $data = ['data_value'];

        /** @var DocumentInterface $document */
        $document = $this->getMockBuilder(DocumentInterface::class)
            ->getMockForAbstractClass();

        $this->metadataProvider->expects($this->once())
            ->method('getRowData')
            ->with($document, [], [])
            ->willReturn($data);
        $this->metadataProvider->expects($this->once())
            ->method('getFields')
            ->with($this->component)
            ->willReturn([]);
        $this->metadataProvider->expects($this->once())
            ->method('getOptions')
            ->willReturn([]);

        $this->filter->expects($this->once())
            ->method('getComponent')
            ->willReturn($this->component);

        $result = $this->model->getRowData($document);
        $this->assertEquals($data, $result);
    }

    /**
     * @return void
     */
    public function testGetXmlFile(): void
    {
        $componentName = 'component_name';

        /** @var DocumentInterface $document */
        $document = $this->getMockBuilder(DocumentInterface::class)
            ->getMockForAbstractClass();

        $this->mockComponent($componentName, $document);
        $this->mockStream();
        $this->mockFilter();
        $this->mockDirectory();
        $this->mockExcel($componentName, $document);

        $this->metadataProvider->expects($this->once())
            ->method('getHeaders')
            ->with($this->component)
            ->willReturn([]);
        $this->metadataProvider->expects($this->once())
            ->method('convertDate')
            ->with($document, $componentName);

        $result = $this->model->getXmlFile();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('rm', $result);
        $this->assertStringContainsString($componentName, $result['value']);
        $this->assertStringContainsString('.xml', $result['value']);
    }

    /**
     * @return void
     */
    protected function mockStream(): void
    {
        $this->stream->expects($this->once())
            ->method('lock')
            ->willReturnSelf();
        $this->stream->expects($this->once())
            ->method('unlock')
            ->willReturnSelf();
        $this->stream->expects($this->once())
            ->method('close')
            ->willReturnSelf();
    }

    /**
     * @param string $componentName
     * @param DocumentInterface $document
     *
     * @return void
     */
    protected function mockExcel(string $componentName, DocumentInterface $document): void
    {
        $searchResultIterator = $this->getMockBuilder(SearchResultIterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $excel = $this->getMockBuilder(Excel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorFactory->expects($this->once())
            ->method('create')
            ->with(['items' => [$document]])
            ->willReturn($searchResultIterator);

        $this->excelFactory->expects($this->once())
            ->method('create')
            ->with([
                'iterator' => $searchResultIterator,
                'rowCallback' => [$this->model, 'getRowData'],
            ])
            ->willReturn($excel);

        $excel->expects($this->once())
            ->method('setDataHeader')
            ->with([])
            ->willReturnSelf();
        $excel->expects($this->once())
            ->method('write')
            ->with($this->stream, $componentName . '.xml')
            ->willReturnSelf();
    }

    /**
     * @param string $componentName
     * @param DocumentInterface|null $document
     *
     * @return void
     */
    protected function mockComponent(string $componentName, ?DocumentInterface $document = null): void
    {
        $context = $this->getMockBuilder(ContextInterface::class)
            ->onlyMethods(['getDataProvider'])
            ->getMockForAbstractClass();

        $dataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->onlyMethods(['getSearchResult', 'setLimit'])
            ->getMockForAbstractClass();

        $searchResult = $this->getMockBuilder(SearchResultInterface::class)
            ->onlyMethods(['getItems'])
            ->getMockForAbstractClass();

        $this->component->expects($this->any())
            ->method('getName')
            ->willReturn($componentName);
        $this->component->expects($this->exactly(2))
            ->method('getContext')
            ->willReturn($context);

        $context->expects($this->exactly(2))
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        $dataProvider->expects($this->once())
            ->method('getSearchResult')
            ->willReturn($searchResult);

        $dataProvider->expects($this->once())
            ->method('setLimit')
            ->with(0, 0);

        if ($document) {
            $searchResult
                ->method('getItems')
                ->willReturn([$document]);
        } else {
            $searchResult
                ->method('getItems')
                ->willReturn([]);
        }
    }

    /**
     * @return void
     */
    protected function mockFilter(): void
    {
        $this->filter->expects($this->once())
            ->method('getComponent')
            ->willReturn($this->component);
        $this->filter->expects($this->once())
            ->method('prepareComponent')
            ->with($this->component)
            ->willReturnSelf();
        $this->filter->expects($this->once())
            ->method('applySelectionOnTargetProvider')
            ->willReturnSelf();
    }

    /**
     * @return void
     */
    protected function mockDirectory(): void
    {
        $this->directory->expects($this->once())
            ->method('create')
            ->with('export')
            ->willReturnSelf();
        $this->directory->expects($this->once())
            ->method('openFile')
            ->willReturn($this->stream);
    }
}
