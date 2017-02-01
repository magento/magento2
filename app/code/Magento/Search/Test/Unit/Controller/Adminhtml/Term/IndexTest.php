<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Controller\Adminhtml\Term;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Search\Controller\Adminhtml\Term\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|\PHPUnit_Framework_MockObject_MockObject
     */
    private $titleMock;

    /**
     * @var Index
     */
    private $indexController;


    public function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->titleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->indexController =  $this->objectManagerHelper->getObject(
            Index::class,
            [
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    public function testIndex()
    {
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($this->pageMock);
        $this->pageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Magento_Search::search_terms')
            ->willReturnSelf();
        $this->pageMock->expects($this->exactly(2))
            ->method('addBreadcrumb')
            ->withConsecutive(
                [__('Search'), __('Search')],
                [__('Search'), __('Search')]
            );
        $this->pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())->method('getTitle')->willReturn($this->titleMock);
        $this->titleMock->expects($this->once())
            ->method('prepend')
            ->with(__('Search Terms'))
            ->willReturn($this->pageMock);

        $this->assertSame($this->pageMock, $this->indexController->execute());
    }
}
