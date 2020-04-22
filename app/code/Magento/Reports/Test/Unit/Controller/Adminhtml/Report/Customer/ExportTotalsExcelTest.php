<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Reports\Controller\Adminhtml\Report\Customer\ExportTotalsExcel;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest;

class ExportTotalsExcelTest extends AbstractControllerTest
{
    /**
     * @var ExportTotalsExcel
     */
    protected $exportTotalsExcel;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->exportTotalsExcel = new ExportTotalsExcel(
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
            ->with('customer_totals.xml', ['export'], DirectoryList::VAR_DIR);
        $this->exportTotalsExcel->execute();
    }
}
