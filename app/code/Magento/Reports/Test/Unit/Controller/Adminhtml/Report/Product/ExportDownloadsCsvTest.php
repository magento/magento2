<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Product\Downloads\Grid;
use Magento\Reports\Controller\Adminhtml\Report\Product\ExportDownloadsCsv;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest;
use PHPUnit\Framework\MockObject\MockObject;

class ExportDownloadsCsvTest extends AbstractControllerTest
{
    /**
     * @var ExportDownloadsCsv
     */
    protected $exportDownloadsCsv;

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
        $this->exportDownloadsCsv = $objectManager->getObject(
            ExportDownloadsCsv::class,
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
            ->method('setSaveParametersInSession')
            ->willReturnSelf();

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
            ->with('products_downloads.csv', $content);

        $this->exportDownloadsCsv->execute();
    }
}
