<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Controller\Adminhtml\Report\Product\ExportSoldExcel;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest;
use PHPUnit\Framework\MockObject\MockObject;

class ExportSoldExcelTest extends AbstractControllerTest
{
    /**
     * @var ExportSoldExcel
     */
    protected $exportSoldExcel;

    /**
     * @var Date|MockObject
     */
    protected $dateMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dateMock = $this->getMockBuilder(Date::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->exportSoldExcel = $objectManager->getObject(
            ExportSoldExcel::class,
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
        $fileName = 'products_ordered.xml';

        $this->abstractBlockMock
            ->expects($this->once())
            ->method('getExcelFile')
            ->with($fileName)
            ->willReturn($content);

        $this->layoutMock
            ->expects($this->once())
            ->method('getChildBlock')
            ->with('adminhtml.report.grid', 'grid.export')
            ->willReturn($this->abstractBlockMock);

        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($fileName, $content, DirectoryList::VAR_DIR);

        $this->exportSoldExcel->execute();
    }
}
