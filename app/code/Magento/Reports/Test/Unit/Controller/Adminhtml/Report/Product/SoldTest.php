<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Title;
use Magento\Reports\Controller\Adminhtml\Report\Product\Sold;
use Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SoldTest extends AbstractControllerTestCase
{
    /**
     * @var Sold
     */
    protected $sold;

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
        $this->sold = $objectManager->getObject(
            Sold::class,
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
        $titleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $titleMock
            ->expects($this->once())
            ->method('prepend')
            ->with(new Phrase('Ordered Products Report'));

        $this->viewMock
            ->expects($this->once())
            ->method('getPage')
            ->willReturn(
                new DataObject(
                    ['config' => new DataObject(
                        ['title' => $titleMock]
                    )]
                )
            );

        $this->menuBlockMock
            ->expects($this->once())
            ->method('setActive')
            ->with('Magento_Reports::report_products_sold');

        $this->breadcrumbsBlockMock
            ->expects($this->exactly(3))
            ->method('addLink')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == new Phrase('Reports') && $arg2 == new Phrase('Reports')) {
                        return null;
                    } elseif ($arg1 == new Phrase('Products') && $arg2 == new Phrase('Products')) {
                        return null;
                    } elseif ($arg1 == new Phrase('Products Ordered') && $arg2 == new Phrase('Products Ordered')) {
                        return null;
                    }
                }
            );
        $this->sold->execute();
    }
}
