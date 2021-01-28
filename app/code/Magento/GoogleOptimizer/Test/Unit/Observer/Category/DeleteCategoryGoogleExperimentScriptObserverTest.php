<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\Category;

class DeleteCategoryGoogleExperimentScriptObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_codeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_category;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var \Magento\GoogleOptimizer\Observer\Category\DeleteCategoryGoogleExperimentScriptObserver
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_codeMock = $this->createMock(\Magento\GoogleOptimizer\Model\Code::class);
        $this->_category = $this->createMock(\Magento\Catalog\Model\Category::class);
        $event = $this->createPartialMock(\Magento\Framework\Event::class, ['getCategory']);
        $event->expects($this->once())->method('getCategory')->willReturn($this->_category);
        $this->_eventObserverMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->willReturn($event);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Magento\GoogleOptimizer\Observer\Category\DeleteCategoryGoogleExperimentScriptObserver::class,
            ['modelCode' => $this->_codeMock]
        );
    }

    public function testDeleteFromCategoryGoogleExperimentScriptSuccess()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_category->expects($this->once())->method('getId')->willReturn($entityId);
        $this->_category->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->willReturn(2);
        $this->_codeMock->expects($this->once())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }

    public function testDeleteFromCategoryGoogleExperimentScriptFail()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_category->expects($this->once())->method('getId')->willReturn($entityId);
        $this->_category->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->willReturn(0);
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }
}
