<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\Product;

class DeleteProductGoogleExperimentScriptObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_codeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var \Magento\GoogleOptimizer\Observer\Product\DeleteProductGoogleExperimentScriptObserver
     */
    protected $_model;

    protected function setUp(): void
    {
        $entityId = 3;
        $storeId = 0;

        $this->_codeMock = $this->createMock(\Magento\GoogleOptimizer\Model\Code::class);
        $event = $this->createPartialMock(\Magento\Framework\Event::class, ['getProduct']);
        $this->_eventObserverMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->willReturn($event);
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId', 'getStoreId', '__wakeup']);
        $product->expects($this->once())->method('getId')->willReturn($entityId);
        $product->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $event->expects($this->once())->method('getProduct')->willReturn($product);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Magento\GoogleOptimizer\Observer\Product\DeleteProductGoogleExperimentScriptObserver::class,
            ['modelCode' => $this->_codeMock]
        );
    }

    public function testDeleteFromProductGoogleExperimentScriptSuccess()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PRODUCT,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->willReturn(2);
        $this->_codeMock->expects($this->once())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }

    public function testDeleteFromProductGoogleExperimentScriptFail()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PRODUCT,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->willReturn(0);
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }
}
