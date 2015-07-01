<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Product;

use Magento\Reports\Controller\Adminhtml\Report\Product\Downloads;
use Magento\Framework\Object;
use Magento\Framework\Phrase;

class DownloadsTest extends \Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest
{
    /**
     * @var \Magento\Reports\Controller\Adminhtml\Report\Product\Downloads
     */
    protected $downloads;

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

        $this->dateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\Filter\Date')
            ->disableOriginalConstructor()
            ->getMock();

        $this->downloads = new Downloads(
            $this->contextMock,
            $this->fileFactoryMock,
            $this->dateMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $titleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();

        $titleMock
            ->expects($this->once())
            ->method('prepend')
            ->with(new Phrase('Downloads Report'));

        $this->viewMock
            ->expects($this->once())
            ->method('getPage')
            ->willReturn(
                new Object(
                    ['config' => new Object(
                        ['title' => $titleMock]
                    )]
                )
            );

        $this->menuBlockMock
            ->expects($this->once())
            ->method('setActive')
            ->with('Magento_Downloadable::report_products_downloads');

        $this->breadcrumbsBlockMock
            ->expects($this->exactly(3))
            ->method('addLink')
            ->withConsecutive(
                [new Phrase('Reports'), new Phrase('Reports')],
                [new Phrase('Products'), new Phrase('Products')],
                [new Phrase('Downloads'), new Phrase('Downloads')]
            );

        $this->layoutMock
            ->expects($this->once())
            ->method('createBlock')
            ->with('Magento\Reports\Block\Adminhtml\Product\Downloads')
            ->willReturn($this->abstractBlockMock);

        $this->downloads->execute();
    }
}
