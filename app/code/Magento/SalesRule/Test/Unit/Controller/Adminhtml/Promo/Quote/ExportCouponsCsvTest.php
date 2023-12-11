<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\ExportCouponsCsv;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportCouponsCsvTest extends TestCase
{
    /**
     * @var ExportCouponsCsv
     */
    private $controller;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * Setup environment
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->fileFactoryMock = $this->createMock(FileFactory::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);

        $this->controller = $this->objectManagerHelper->getObject(
            ExportCouponsCsv::class,
            [
                'fileFactory' => $this->fileFactoryMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    /**
     * Test execute function
     */
    public function testExecute()
    {
        $fileName = 'coupon_codes.csv';

        $resultLayoutMock = $this->createMock(Layout::class);
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $contentMock = $this->getMockBuilder(AbstractBlock::class)
            ->addMethods(['getCsvFile'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT)->willReturn($resultLayoutMock);
        $resultLayoutMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $layoutMock->expects($this->once())->method('createBlock')->with(Grid::class)
            ->willReturn($contentMock);
        $contentMock->expects($this->once())->method('getCsvFile')->willReturn('csvFile');
        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($fileName, 'csvFile', DirectoryList::VAR_DIR);

        $this->controller->execute();
    }
}
