<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Model\Export;

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

    public function setUp()
    {
        $this->directory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMockForAbstractClass();

        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->directory);

        $this->filter = $this->getMockBuilder('Magento\Ui\Component\MassAction\Filter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataProvider = $this->getMockBuilder('Magento\Ui\Model\Export\MetadataProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->excelFactory = $this->getMockBuilder('Magento\Framework\Convert\ExcelFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->iteratorFactory = $this->getMockBuilder('Magento\Ui\Model\Export\SearchResultIteratorFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->component = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentInterface')
            ->getMockForAbstractClass();

        $this->stream = $this->getMockBuilder('Magento\Framework\Filesystem\File\WriteInterface')
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

        $document = $this->getMockBuilder('Magento\Framework\Api\Search\DocumentInterface')
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

        $document = $this->getMockBuilder('Magento\Framework\Api\Search\DocumentInterface')
            ->getMockForAbstractClass();

        $this->mockComponent($componentName, $document);
        $this->mockStream();
        $this->mockFilter();
        $this->mockDirectory();
        $this->mockExcel($componentName);

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
     */
    protected function mockExcel($componentName)
    {
        $searchResultIterator = $this->getMockBuilder('Magento\Ui\Model\Export\SearchResultIterator')
            ->disableOriginalConstructor()
            ->getMock();

        $excel = $this->getMockBuilder('Magento\Framework\Convert\Excel')
            ->disableOriginalConstructor()
            ->getMock();

        $this->iteratorFactory->expects($this->once())
            ->method('create')
            ->with(['items' => []])
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
     * @param null|object $document
     */
    protected function mockComponent($componentName, $document = null)
    {
        $context = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->setMethods(['getDataProvider'])
            ->getMockForAbstractClass();

        $dataProvider = $this->getMockBuilder(
            'Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface'
        )
            ->setMethods(['getSearchResult'])
            ->getMockForAbstractClass();

        $searchResult = $this->getMockBuilder('Magento\Framework\Api\Search\SearchResultInterface')
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();

        $this->component->expects($this->any())
            ->method('getName')
            ->willReturn($componentName);
        $this->component->expects($this->once())
            ->method('getContext')
            ->willReturn($context);

        $context->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        $dataProvider->expects($this->once())
            ->method('getSearchResult')
            ->willReturn($searchResult);

        if ($document) {
            $searchResult->expects($this->at(0))
                ->method('getItems')
                ->willReturn([$document]);
            $searchResult->expects($this->at(1))
                ->method('getItems')
                ->willReturn([]);
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
