<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var \Magento\Framework\Model\ExtensionAttributesFactory
     */
    private $customAttributeFactory;

    /**
     * @var AbstractResource
     */
    private $resource;

    /**
     * @var AbstractDb
     */
    private $resourceCollection;

    /**
     * @var MockObject
     */
    private $eventDispatcher;

    /**
     * @var Stock
     */
    private $stockModel;

    protected function setUp(): void
    {
        /** @var  MockObject */
        $this->eventDispatcher = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEventDispatcher'])
            ->getMock();
        $this->context->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventDispatcher);

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customAttributeFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIdFieldName'])
            ->getMockForAbstractClass();

        $this->resourceCollection = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockModel = new Stock(
            $this->context,
            $this->registry,
            $this->extensionFactory,
            $this->customAttributeFactory,
            $this->resource,
            $this->resourceCollection
        );
    }

    /**
     * We want to ensure that property $_eventPrefix used during event dispatching
     *
     * @param $eventName
     * @param $methodName
     * @param $objectName
     *
     * @dataProvider eventsDataProvider
     */
    public function testDispatchEvents($eventName, $methodName, $objectName)
    {
        $isCalledWithRightPrefix = 0;
        $isObjectNameRight = 0;
        $this->eventDispatcher->expects($this->any())->method('dispatch')->with(
            $this->callback(function ($arg) use (&$isCalledWithRightPrefix, $eventName) {
                $isCalledWithRightPrefix |= ($arg === $eventName);
                return true;
            }),
            $this->callback(function ($data) use (&$isObjectNameRight, $objectName) {
                $isObjectNameRight |= isset($data[$objectName]);
                return true;
            })
        );

        $this->stockModel->$methodName();
        $this->assertTrue(
            ($isCalledWithRightPrefix && $isObjectNameRight),
            sprintf('Event "%s" with object name "%s" doesn\'t dispatched properly', $eventName, $objectName)
        );
    }

    /**
     * @return array
     */
    public static function eventsDataProvider()
    {
        return [
            ['cataloginventory_stock_save_before', 'beforeSave', 'stock'],
            ['cataloginventory_stock_save_after', 'afterSave', 'stock'],
        ];
    }
}
