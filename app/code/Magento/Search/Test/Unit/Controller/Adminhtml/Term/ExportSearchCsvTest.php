<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Term;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Search\Controller\Adminhtml\Term\ExportSearchCsv;
use Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportPost;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportSearchCsvTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ExportPost
     */
    private $controller;

    /**
     * @var MockObject
     */
    private $fileFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var MockObject
     */
    private $resultFactoryMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->fileFactoryMock = $this->createMock(FileFactory::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);

        $this->controller = $this->objectManagerHelper->getObject(
            ExportSearchCsv::class,
            [
                'fileFactory' => $this->fileFactoryMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    public function testExecute()
    {
        $resultLayoutMock = $this->createMock(Layout::class);
        $layoutMock = $this->createMock(LayoutInterface::class);
        $contentMock = $this->createPartialMockWithReflection(AbstractBlock::class, ['getCsvFile']);
        $contentMock->method('getCsvFile')->willReturn('csvFile');
        $this->resultFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT)->willReturn($resultLayoutMock);
        $resultLayoutMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('getChildBlock')->willReturn($contentMock);
        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('search.csv', 'csvFile', DirectoryList::VAR_DIR);
        $this->controller->execute();
    }
}
