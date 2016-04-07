<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Customer;

use Magento\Reports\Controller\Adminhtml\Report\Customer\ExportOrdersExcel;

class ExportOrdersExcelTest extends \Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest
{
    /**
     * @var \Magento\Reports\Controller\Adminhtml\Report\Customer\ExportOrdersExcel
     */
    protected $exportOrdersExcel;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->exportOrdersExcel = new ExportOrdersExcel(
            $this->contextMock,
            $this->fileFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $this->abstractBlockMock
            ->expects($this->once())
            ->method('getExcelFile')
            ->willReturn(['export']);
        $this->layoutMock
            ->expects($this->once())
            ->method('getChildBlock')
            ->with('adminhtml.report.grid', 'grid.export');
        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('customers_orders.xml', ['export'], \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $this->exportOrdersExcel->execute();
    }
}
