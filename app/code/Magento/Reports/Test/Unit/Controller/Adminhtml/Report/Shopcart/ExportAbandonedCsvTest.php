<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Shopcart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Shopcart\Abandoned\Grid;
use Magento\Reports\Controller\Adminhtml\Report\Shopcart\ExportAbandonedCsv;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest;

class ExportAbandonedCsvTest extends AbstractControllerTest
{
    /**
     * @var ExportAbandonedCsv
     */
    protected $exportAbandonedCsv;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = new ObjectManager($this);
        $this->exportAbandonedCsv = $objectManager->getObject(
            ExportAbandonedCsv::class,
            [
                'context' => $this->contextMock,
                'fileFactory' => $this->fileFactoryMock,
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
            ->method('getCsv')
            ->willReturn($content);

        $this->layoutMock
            ->expects($this->once())
            ->method('createBlock')
            ->with(Grid::class)
            ->willReturn($this->abstractBlockMock);

        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with('shopcart_abandoned.csv', $content);

        $this->exportAbandonedCsv->execute();
    }
}
