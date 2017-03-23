<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Customer;

use Magento\Reports\Controller\Adminhtml\Report\Customer\ExportAccountsCsv;

class ExportAccountsCsvTest extends \Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest
{
    /**
     * @var \Magento\Reports\Controller\Adminhtml\Report\Customer\ExportAccountsCsv
     */
    protected $exportAccountsCsv;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
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
            ->with('new_accounts.csv', ['export'], \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $this->exportAccountsCsv->execute();
    }
}
