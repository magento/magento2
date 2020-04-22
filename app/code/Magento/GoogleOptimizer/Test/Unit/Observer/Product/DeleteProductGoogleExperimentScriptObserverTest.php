<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Model\Code;
use Magento\GoogleOptimizer\Observer\Product\DeleteProductGoogleExperimentScriptObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteProductGoogleExperimentScriptObserverTest extends TestCase
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
     * @var DeleteProductGoogleExperimentScriptObserver
     */
    protected $_model;

    protected function setUp(): void
    {
        $entityId = 3;
        $storeId = 0;

        $this->_codeMock = $this->createMock(Code::class);
        $event = $this->createPartialMock(Event::class, ['getProduct']);
        $this->_eventObserverMock = $this->createMock(Observer::class);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $product = $this->createPartialMock(Product::class, ['getId', 'getStoreId', '__wakeup']);
        $product->expects($this->once())->method('getId')->will($this->returnValue($entityId));
        $product->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $event->expects($this->once())->method('getProduct')->will($this->returnValue($product));

        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            DeleteProductGoogleExperimentScriptObserver::class,
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
            Code::ENTITY_TYPE_PRODUCT,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue(2));
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
            Code::ENTITY_TYPE_PRODUCT,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue(0));
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }
}
