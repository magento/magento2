<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToXml;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Model\Export\SearchResultIterator;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConvertToXmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConvertToXml
     */
    protected $model;

    /**
     * @var Filesystem | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var Filter | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * @var MetadataProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataProvider;

    /**
     * @var ExcelFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $excelFactory;

    /**
     * @var SearchResultIteratorFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $iteratorFactory;

    /**
     * @var DirectoryWriteInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directory;

    /**
     * @var FileWriteInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stream;

    /**
     * @var UiComponentInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $component;

    protected function setUp()
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
            ->setMethods(['create'])
            ->getMock();

        $this->iteratorFactory = $this->getMockBuilder(\Magento\Ui\Model\Export\SearchResultIteratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->component = $this->getMockBuilder(UiComponentInterface::class)
            ->getMockForAbstractClass();

        $this->stream = $this->getMockBuilder(FileWriteInterface::class)
            ->setMethods([
                'lock',
                'unlock',
                'close',
            ])
            ->getMockForAbstractClass();

        $this->model = new ConvertToXml(
            $this->filesystem,
            $this->filter,
            $this->metadataProvider,
            $this->excelFactory,
            $this->iteratorFactory
        );
    }

    public function testGetRowData()
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

    public function testGetXmlFile()
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
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('rm', $result);
        $this->assertContains($componentName, $result);
        $this->assertContains('.xml', $result);
    }

    protected function mockStream()
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
     */
    protected function mockExcel($componentName, DocumentInterface $document)
    {
        $searchResultIterator = $this->getMockBuilder(SearchResultIterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $excel = $this->getMockBuilder(\Magento\Framework\Convert\Excel::class)
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
     */
    protected function mockComponent($componentName, DocumentInterface $document = null)
    {
        $context = $this->getMockBuilder(ContextInterface::class)
            ->setMethods(['getDataProvider'])
            ->getMockForAbstractClass();

        $dataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->setMethods(['getSearchResult', 'setLimit'])
            ->getMockForAbstractClass();

        $searchResult = $this->getMockBuilder(SearchResultInterface::class)
            ->setMethods(['getItems'])
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
            $searchResult->expects($this->at(0))
                ->method('getItems')
                ->willReturn([$document]);
        } else {
            $searchResult->expects($this->at(0))
                ->method('getItems')
                ->willReturn([]);
        }
    }

    protected function mockFilter()
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

    protected function mockDirectory()
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
