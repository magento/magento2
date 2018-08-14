<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Reports\Controller\Adminhtml\Report\Product\ExportLowstockExcel;

class ExportLowstockExcelTest extends \Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest
{
    /**
     * @var \Magento\Reports\Controller\Adminhtml\Report\Product\ExportLowstockExcel
     */
    protected $exportLowstockExcel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\Filter\Date::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->exportLowstockExcel = $objectManager->getObject(
            \Magento\Reports\Controller\Adminhtml\Report\Product\ExportLowstockExcel::class,
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

        $this->abstractBlockMock
            ->expects($this->once())
            ->method('getExcelFile')
            ->willReturn($content);

        $this->layoutMock
            ->expects($this->once())
            ->method('getChildBlock')
            ->with('adminhtml.block.report.product.lowstock.grid', 'grid.export')
            ->willReturn($this->abstractBlockMock);

        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('products_lowstock.xml', $content, \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);

        $this->exportLowstockExcel->execute();
    }
}
