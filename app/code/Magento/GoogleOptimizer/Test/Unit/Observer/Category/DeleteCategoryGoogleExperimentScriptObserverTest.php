<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Model\Code;
use Magento\GoogleOptimizer\Observer\Category\DeleteCategoryGoogleExperimentScriptObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteCategoryGoogleExperimentScriptObserverTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_codeMock;

    /**
     * @var MockObject
     */
    protected $_category;

    /**
     * @var MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var DeleteCategoryGoogleExperimentScriptObserver
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_codeMock = $this->createMock(Code::class);
        $this->_category = $this->createMock(Category::class);
        $event = $this->createPartialMock(Event::class, ['getCategory']);
        $event->expects($this->once())->method('getCategory')->will($this->returnValue($this->_category));
        $this->_eventObserverMock = $this->createMock(Observer::class);
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));

        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            DeleteCategoryGoogleExperimentScriptObserver::class,
            ['modelCode' => $this->_codeMock]
        );
    }

    public function testDeleteFromCategoryGoogleExperimentScriptSuccess()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_category->expects($this->once())->method('getId')->will($this->returnValue($entityId));
        $this->_category->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            Code::ENTITY_TYPE_CATEGORY,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue(2));
        $this->_codeMock->expects($this->once())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }

    public function testDeleteFromCategoryGoogleExperimentScriptFail()
    {
        $entityId = 3;
        $storeId = 0;

        $this->_category->expects($this->once())->method('getId')->will($this->returnValue($entityId));
        $this->_category->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));

        $this->_codeMock->expects(
            $this->once()
        )->method(
            'loadByEntityIdAndType'
        )->with(
            $entityId,
            Code::ENTITY_TYPE_CATEGORY,
            $storeId
        );
        $this->_codeMock->expects($this->once())->method('getId')->will($this->returnValue(0));
        $this->_codeMock->expects($this->never())->method('delete');

        $this->_model->execute($this->_eventObserverMock);
    }
}
