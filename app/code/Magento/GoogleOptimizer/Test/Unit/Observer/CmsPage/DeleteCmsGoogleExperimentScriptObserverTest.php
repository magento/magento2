<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleOptimizer\Test\Unit\Observer\CmsPage;

use Magento\Cms\Model\Page;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Model\Code;
use Magento\GoogleOptimizer\Observer\CmsPage\DeleteCmsGoogleExperimentScriptObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteCmsGoogleExperimentScriptObserverTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_codeMock;

    /**
     * @var MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var DeleteCmsGoogleExperimentScriptObserver
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_codeMock = $this->createMock(Code::class);
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('getId')->willReturn(3);
        $event = $this->getMockBuilder(Event::class)
            ->addMethods(['getObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getObject')->willReturn($page);
        $this->_eventObserverMock = $this->createMock(Observer::class);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->willReturn($event);

        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            DeleteCmsGoogleExperimentScriptObserver::class,
            ['modelCode' => $this->_codeMock]
        );
    }

    public function testDeleteFromPageGoogleExperimentScriptSuccess()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            Code::ENTITY_TYPE_PAGE,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->willReturn(2);
        $this->_codeMock->expects($this->once())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }

    public function testDeleteFromPageGoogleExperimentScriptFail()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            Code::ENTITY_TYPE_PAGE,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->willReturn(0);
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }
}
