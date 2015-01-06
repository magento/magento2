<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Resource\Eav;

use \Magento\Catalog\Model\Resource\Eav\Attribute;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Eav\Attribute
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

    public function setUp()
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
            'Magento\Framework\Model\Resource\AbstractResource',
            ['_construct', '_getReadAdapter', '_getWriteAdapter', 'getIdFieldName',
                'save', 'saveInSetIncluding', 'isUsedBySuperProducts', 'delete'],
            [], '', false
        );

        $this->resourceMock->expects($this->any())
            ->method('_getWriteAdapter')
            ->will($this->returnValue($dbAdapterMock));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(
                'Magento\Catalog\Model\Resource\Eav\Attribute',
                [
                    'context' => $this->contextMock,
                    'productFlatIndexerProcessor' => $this->_processor,
                    'indexerEavProcessor' => $this->_eavProcessor,
                    'resource' => $this->resourceMock,
                    'data' => ['id' => 1]
                ]
        );
    }

    public function testIndexerAfterSaveAttribute()
    {
        $this->_processor->expects($this->once())->method('markIndexerAsInvalid');

        $this->_model->setData(['id' => 2, 'used_in_product_listing' => 1]);

        $this->_model->afterSave();
    }

    public function testIndexerAfterSaveScopeChangeAttribute()
    {
        $this->_processor->expects($this->once())->method('markIndexerAsInvalid');

        $this->_model->setOrigData('is_global', \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE);
        $this->_model->setOrigData('used_in_product_listing', 1);
        $this->_model->setIsGlobal(\Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL);
        $this->_model->afterSave();
    }

    public function testIndexerAfterDeleteAttribute()
    {
        $this->_processor->expects($this->once())->method('markIndexerAsInvalid');
        $this->_model->setOrigData('id', 2);
        $this->_model->setOrigData('used_in_product_listing', 1);
        $this->_model->afterDeleteCommit();
    }

    public function testGetScopeGlobal()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Resource\Eav\Attribute',
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => [
                    'is_global' => Attribute::SCOPE_GLOBAL
                ]
            ]
        );
        $this->assertEquals('global', $this->_model->getScope());
    }

    public function testGetScopeWebiste()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Resource\Eav\Attribute',
            [
                'context' => $this->contextMock,
                'productFlatIndexerProcessor' => $this->_processor,
                'indexerEavProcessor' => $this->_eavProcessor,
                'resource' => $this->resourceMock,
                'data' => [
                    'is_global' => Attribute::SCOPE_WEBSITE
                ]
            ]
        );
        $this->assertEquals('website', $this->_model->getScope());
    }

    public function testGetScopeStore()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            '\Magento\Catalog\Model\Resource\Eav\Attribute',
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
