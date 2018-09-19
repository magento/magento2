<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\CmsPage;

class DeleteCmsGoogleExperimentScriptObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_codeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var \Magento\GoogleOptimizer\Observer\CmsPage\DeleteCmsGoogleExperimentScriptObserver
     */
    protected $_model;

    protected function setUp()
    {
        $this->_codeMock = $this->createMock(\Magento\GoogleOptimizer\Model\Code::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $page = $this->createMock(\Magento\Cms\Model\Page::class);
        $page->expects($this->once())->method('getId')->will($this->returnValue(3));
        $event = $this->createPartialMock(\Magento\Framework\Event::class, ['getObject']);
        $event->expects($this->once())->method('getObject')->will($this->returnValue($page));
        $this->_eventObserverMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Magento\GoogleOptimizer\Observer\CmsPage\DeleteCmsGoogleExperimentScriptObserver::class,
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
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue(2));
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
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue(0));
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }
}
