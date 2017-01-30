<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Eav;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_processor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_eavProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    protected function setUp()
    {
        $this->_processor = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor',
            [],
            [],
            '',
            false
        );

        $this->_eavProcessor = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Eav\Processor',
            [],
            [],
            '',
            false
        );

        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);

        $cacheInterfaceMock = $this->getMock('Magento\Framework\App\CacheInterface', [], [], '', false);

        $actionValidatorMock = $this->getMock(
            '\Magento\Framework\Model\ActionValidator\RemoveAction', [], [], '', false
        );
        $actionValidatorMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $this->contextMock = $this->getMock(
            '\Magento\Framework\Model\Context',
            ['getEventDispatcher', 'getCacheManager', 'getActionValidator'], [], '', false
        );

        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($eventManagerMock));
        $this->contextMock->expects($this->any())
            ->method('getCacheManager')
            ->will($this->returnValue($cacheInterfaceMock));
        $this->contextMock->expects($this->any())->method('getActionValidator')
            ->will($this->returnValue($actionValidatorMock));

        $dbAdapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $dbAdapterMock->expects($this->any())->method('getTransactionLevel')->will($this->returnValue(1));

        $this->resourceMock = $this->getMock(
            'Magento\Framework\Model\ResourceModel\AbstractResource',
            ['_construct', 'getConnection', 'getIdFieldName',
                'save', 'saveInSetIncluding', 'isUsedBySuperProducts', 'delete'],
            [], '', false
        );

        $this->eavConfigMock = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->setMethods(['clear'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($dbAdapterMock));

        $attributeCacheMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\AttributeCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(
                'Magento\Catalog\Model\ResourceModel\Eav\Attribute',
                [
                    'context' => $this->contextMock,
                    'productFlatIndexerProcessor' => $this->_processor,
                    'indexerEavProcessor' => $this->_eavProcessor,
                    'resource' => $this->resourceMock,
                    'data' => ['id' => 1],
                    'eavConfig' => $this->eavConfigMock,
                    'attributeCache' => $attributeCacheMock
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

        $this->_model->setOrigData('is_global', \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE);
        $this->_model->setOrigData('used_in_product_listing', 1);
        $this->_model->setIsGlobal(\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL);
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
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => [
                    'is_global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
                ]
            ]
        );
        $this->assertEquals('global', $this->_model->getScope());
    }

    public function testGetScopeWebiste()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\ResourceModel\Eav\Attribute',
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => [
                    'is_global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE
                ]
            ]
        );
        $this->assertEquals('website', $this->_model->getScope());
    }

    public function testGetScopeStore()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\ResourceModel\Eav\Attribute',
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
