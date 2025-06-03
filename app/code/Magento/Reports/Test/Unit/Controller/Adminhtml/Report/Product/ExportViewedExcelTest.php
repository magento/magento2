<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Backend\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Product\Viewed\Grid;
use Magento\Reports\Controller\Adminhtml\Report\Product\ExportViewedExcel;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ExportViewedExcelTest extends AbstractControllerTestCase
{
    /**
     * @var ExportViewedExcel
     */
    protected $exportViewedExcel;

    /**
     * @var Date|MockObject
     */
    protected $dateMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Data|MockObject
     */
    protected $helperMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dateMock = $this->getMockBuilder(Date::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->helperMock);

        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);

        $objectManager = new ObjectManager($this);
        $this->exportViewedExcel = $objectManager->getObject(
            ExportViewedExcel::class,
            [
                'context' => $this->contextMock,
                'fileFactory' => $this->fileFactoryMock,
                'dateFilter' => $this->dateMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $content = ['export'];
        $fileName = 'products_mostviewed.xml';

        $this->abstractBlockMock
            ->expects($this->once())
            ->method('getExcelFile')
            ->with($fileName)
            ->willReturn($content);

        $this->layoutMock
            ->expects($this->once())
            ->method('createBlock')
            ->with(Grid::class)
            ->willReturn($this->abstractBlockMock);

        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($fileName, $content, DirectoryList::VAR_DIR);

        $this->exportViewedExcel->execute();
    }
}
