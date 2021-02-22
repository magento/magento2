<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Controller\Adminhtml\System\Store;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Controller\Adminhtml\System\Store\Index;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Page|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pageMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|\PHPUnit\Framework\MockObject\MockObject
     */
    private $titleMock;

    /**
     * @var Index
     */
    private $indexController;

    protected function setUp(): void
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
            ->with('Magento_Backend::system_store')
            ->willReturnSelf();
        $this->pageMock->expects($this->exactly(2))
            ->method('addBreadcrumb')
            ->withConsecutive(
                [__('Stores'), __('Stores')],
                [__('All Stores'), __('All Stores')]
            );
        $this->pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())->method('getTitle')->willReturn($this->titleMock);
        $this->titleMock->expects($this->once())->method('prepend')->with(__('Stores'))->willReturn($this->pageMock);

        $this->assertSame($this->pageMock, $this->indexController->execute());
    }
}
