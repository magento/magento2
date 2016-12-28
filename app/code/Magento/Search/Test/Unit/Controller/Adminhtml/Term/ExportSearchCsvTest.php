<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Controller\Adminhtml\Term;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportSearchCsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TaxImportExport\Controller\Adminhtml\Rate\ExportPost
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->fileFactoryMock = $this->getMock(
            \Magento\Framework\App\Response\Http\FileFactory::class,
            [],
            [],
            '',
            false
        );
        $this->resultFactoryMock = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            [],
            [],
            '',
            false
        );

        $this->controller = $this->objectManagerHelper->getObject(
            \Magento\Search\Controller\Adminhtml\Term\ExportSearchCsv::class,
            [
                'fileFactory' => $this->fileFactoryMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    public function testExecute()
    {
        $resultLayoutMock = $this->getMock(\Magento\Framework\View\Result\Layout::class, [], [], '', false);
        $layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $contentMock = $this->getMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['getCsvFile'],
            [],
            '',
            false
        );
        $this->resultFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT)->willReturn($resultLayoutMock);
        $resultLayoutMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('getChildBlock')->willReturn($contentMock);
        $contentMock->expects($this->once())->method('getCsvFile')->willReturn('csvFile');
        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('search.csv', 'csvFile', DirectoryList::VAR_DIR);
        $this->controller->execute();
    }
}
