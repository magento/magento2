<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Customer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Reports\Controller\Adminhtml\Report\Customer\ExportAccountsCsv;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest;

class ExportAccountsCsvTest extends AbstractControllerTest
{
    /**
     * @var ExportAccountsCsv
     */
    protected $exportAccountsCsv;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->exportAccountsCsv = new ExportAccountsCsv(
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
            ->method('getCsvFile')
            ->willReturn(['export']);
        $this->layoutMock
            ->expects($this->once())
            ->method('getChildBlock')
            ->with('adminhtml.report.grid', 'grid.export');
        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('new_accounts.csv', ['export'], DirectoryList::VAR_DIR);
        $this->exportAccountsCsv->execute();
    }
}
