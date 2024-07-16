<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Eav;

use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends TestCase
{
    /**
     * @var Attribute
     */
    protected $_model;

    /**
     * @var Processor
     */
    protected $_processor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_eavProcessor;

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    /**
     * @var Config|MockObject
     */
    protected $eavConfigMock;

    protected function setUp(): void
    {
        $this->_processor = $this->createMock(Processor::class);

        $this->_eavProcessor = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Eav\Processor::class);

        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $cacheInterfaceMock = $this->getMockForAbstractClass(CacheInterface::class);

        $actionValidatorMock = $this->createMock(RemoveAction::class);
        $actionValidatorMock->expects($this->any())->method('isAllowed')->willReturn(true);

        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getEventDispatcher', 'getCacheManager', 'getActionValidator']
        );

        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getCacheManager')
            ->willReturn($cacheInterfaceMock);
        $this->contextMock->expects($this->any())->method('getActionValidator')
            ->willReturn($actionValidatorMock);

        $dbAdapterMock = $this->createMock(Mysql::class);

        $dbAdapterMock->expects($this->any())->method('getTransactionLevel')->willReturn(1);

        $this->resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['getIdFieldName', 'save', 'saveInSetIncluding', 'isUsedBySuperProducts', 'delete'])
            ->onlyMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['clear'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($dbAdapterMock);

        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(
            Attribute::class,
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => ['id' => 1],
                'eavConfig' => $this->eavConfigMock
            ]
        );
    }

    public function testIndexerAfterSaveAttribute()
    {
        $this->_processor->expects($this->once())->method('markIndexerAsInvalid');

        $this->_model->setData('id', 2);
        $this->_model->setData('used_in_product_listing', 1);

        $this->_model->afterSave();
    }

    public function testIndexerAfterSaveScopeChangeAttribute()
    {
        $this->_processor->expects($this->once())->method('markIndexerAsInvalid');

        $this->_model->setOrigData(
            'is_global',
            ScopedAttributeInterface::SCOPE_STORE
        );
        $this->_model->setOrigData('used_in_product_listing', 1);
        $this->_model->setIsGlobal(ScopedAttributeInterface::SCOPE_GLOBAL);
        $this->_model->afterSave();
    }

    public function testAfterSaveEavCache()
    {
        $this->eavConfigMock
            ->expects($this->once())
            ->method('clear');
        $this->_model->afterSave();
    }

    public function testIndexerAfterDeleteAttribute()
    {
        $this->_processor->expects($this->once())->method('markIndexerAsInvalid');
        $this->_model->setOrigData('id', 2);
        $this->_model->setOrigData('used_in_product_listing', 1);
        $this->_model->afterDeleteCommit();
    }

    public function testAfterDeleteEavCache()
    {
        $this->eavConfigMock
            ->expects($this->once())
            ->method('clear');
        $this->_model->afterDelete();
    }

    public function testGetScopeGlobal()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            Attribute::class,
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => [
                    'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            ]
        );
        $this->assertEquals('global', $this->_model->getScope());
    }

    public function testGetScopeWebsite()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            Attribute::class,
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => [
                    'is_global' => ScopedAttributeInterface::SCOPE_WEBSITE
                ]
            ]
        );
        $this->assertEquals('website', $this->_model->getScope());
    }

    public function testGetScopeStore()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            Attribute::class,
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => [
                    'is_global' => 'some value'
                ]
            ]
        );
        $this->assertEquals('store', $this->_model->getScope());
    }
}
