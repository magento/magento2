<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Entity;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $eventManagerMock->expects($this->any())->method('dispatch');
        $this->contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
    }

    /**
     * @covers \Magento\Eav\Model\ResourceModel\Entity\Attribute::_saveOption
     */
    public function testSaveOptionSystemAttribute()
    {
        /** @var $connectionMock \PHPUnit\Framework\MockObject\MockObject */
        /** @var $resourceModel \Magento\Eav\Model\ResourceModel\Entity\Attribute */
        list($connectionMock, $resourceModel) = $this->_prepareResourceModel();

        $attributeData = [
            'attribute_id' => '123',
            'entity_type_id' => 4,
            'attribute_code' => 'status',
            'backend_model' => null,
            'backend_type' => 'int',
            'frontend_input' => 'select',
            'frontend_label' => 'Status',
            'frontend_class' => null,
            'source_model' => \Magento\Catalog\Model\Product\Attribute\Source\Status::class,
            'is_required' => 1,
            'is_user_defined' => 0,
            'is_unique' => 0
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var $model \Magento\Framework\Model\AbstractModel */
        $arguments = $objectManagerHelper->getConstructArguments(\Magento\Framework\Model\AbstractModel::class);
        $arguments['data'] = $attributeData;
        $arguments['context'] = $this->contextMock;

        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['hasDataChanges'])
            ->setConstructorArgs($arguments)
            ->getMock();
        $model->setDefault(['2']);
        $model->setOption(['delete' => [1 => '', 2 => '']]);
        $model->expects($this->any())->method('hasDataChanges')->willReturn(true);

        $connectionMock->expects(
            $this->once()
        )->method(
            'insert'
        )->willReturnMap(
            [['eav_attribute', $attributeData, 1]]
        );

        $connectionMock->expects(
            $this->once()
        )->method(
            'fetchRow'
        )->willReturnMap(
            
                [
                    [
                        'SELECT `eav_attribute`.* FROM `eav_attribute` ' .
                        'WHERE (attribute_code="status") AND (entity_type_id="4")',
                        $attributeData,
                    ],
                ]
            
        );
        $connectionMock->expects(
            $this->once()
        )->method(
            'update'
        )->with(
            'eav_attribute',
            ['default_value' => 2],
            ['attribute_id = ?' => null]
        );
        $connectionMock->expects($this->never())->method('delete');

        $resourceModel->save($model);
    }

    /**
     * @covers \Magento\Eav\Model\ResourceModel\Entity\Attribute::_saveOption
     */
    public function testSaveOptionNewUserDefinedAttribute()
    {
        /** @var $connectionMock \PHPUnit\Framework\MockObject\MockObject */
        /** @var $resourceModel \Magento\Eav\Model\ResourceModel\Entity\Attribute */
        list($connectionMock, $resourceModel) = $this->_prepareResourceModel();

        $attributeData = [
            'entity_type_id' => 4,
            'attribute_code' => 'a_dropdown',
            'backend_model' => null,
            'backend_type' => 'int',
            'frontend_input' => 'select',
            'frontend_label' => 'A Dropdown',
            'frontend_class' => null,
            'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
            'is_required' => 0,
            'is_user_defined' => 1,
            'is_unique' => 0,
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var $model \Magento\Framework\Model\AbstractModel */
        $arguments = $objectManagerHelper->getConstructArguments(\Magento\Framework\Model\AbstractModel::class);
        $arguments['data'] = $attributeData;
        $arguments['context'] = $this->contextMock;
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['hasDataChanges'])
            ->setConstructorArgs($arguments)
            ->getMock();
        $model->expects($this->any())->method('hasDataChanges')->willReturn(true);

        $model->setOption(['value' => ['option_1' => ['Backend Label', 'Frontend Label']]]);

        $connectionMock->expects(
            $this->any()
        )->method(
            'lastInsertId'
        )->willReturnMap(
            [['eav_attribute', 123], ['eav_attribute_option_value', 321]]
        );
        $connectionMock->expects(
            $this->once()
        )->method(
            'update'
        )->willReturnMap(
            
                [['eav_attribute', ['default_value' => ''], ['attribute_id = ?' => 123], 1]]
            
        );
        $connectionMock->expects(
            $this->once()
        )->method(
            'fetchRow'
        )->willReturnMap(
            
                [
                    [
                        'SELECT `eav_attribute`.* FROM `eav_attribute` ' .
                        'WHERE (attribute_code="a_dropdown") AND (entity_type_id="4")',
                        false,
                    ],
                ]
            
        );
        $connectionMock->expects(
            $this->once()
        )->method(
            'delete'
        )->willReturnMap(
            [['eav_attribute_option_value', ['option_id = ?' => ''], 0]]
        );
        $connectionMock->expects(
            $this->exactly(4)
        )->method(
            'insert'
        )->willReturnMap(
            
                [
                    ['eav_attribute', $attributeData, 1],
                    ['eav_attribute_option', ['attribute_id' => 123, 'sort_order' => 0], 1],
                    [
                        'eav_attribute_option_value',
                        ['option_id' => 123, 'store_id' => 0, 'value' => 'Backend Label'],
                        1
                    ],
                    [
                        'eav_attribute_option_value',
                        ['option_id' => 123, 'store_id' => 1, 'value' => 'Frontend Label'],
                        1
                    ],
                ]
            
        );
        $connectionMock->expects($this->any())->method('getTransactionLevel')->willReturn(1);

        $resourceModel->save($model);
    }

    /**
     * @covers \Magento\Eav\Model\ResourceModel\Entity\Attribute::_saveOption
     */
    public function testSaveOptionNoValue()
    {
        /** @var $connectionMock \PHPUnit\Framework\MockObject\MockObject */
        /** @var $resourceModel \Magento\Eav\Model\ResourceModel\Entity\Attribute */
        list($connectionMock, $resourceModel) = $this->_prepareResourceModel();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var $model \Magento\Framework\Model\AbstractModel */
        $arguments = $objectManagerHelper->getConstructArguments(\Magento\Framework\Model\AbstractModel::class);
        $arguments['context'] = $this->contextMock;
        $model = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['hasDataChanges'])
            ->setConstructorArgs($arguments)
            ->getMock();
        $model->expects($this->any())->method('hasDataChanges')->willReturn(true);
        $model->setOption('not-an-array');

        $connectionMock->expects($this->once())->method('insert')->with('eav_attribute');
        $connectionMock->expects($this->never())->method('delete');
        $connectionMock->expects($this->never())->method('update');

        $resourceModel->save($model);
    }

    /**
     * Retrieve resource model mock instance and its adapter
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareResourceModel()
    {
        $connectionMock = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [
                '_connect',
                'delete',
                'describeTable',
                'fetchRow',
                'insert',
                'lastInsertId',
                'quote',
                'update',
                'beginTransaction',
                'commit',
                'rollback',
                'select',
                'getTransactionLevel'
            ]);
        $connectionMock->expects(
            $this->any()
        )->method(
            'describeTable'
        )->with(
            'eav_attribute'
        )->willReturn(
            $this->_describeEavAttribute()
        );
        $connectionMock->expects(
            $this->any()
        )->method(
            'quote'
        )->willReturnMap(
            
                [
                    [123, 123],
                    ['4', '"4"'],
                    ['a_dropdown', '"a_dropdown"'],
                    ['status', '"status"'],
                ]
            
        );
        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $connectionMock->expects(
            $this->any()
        )->method(
            'select'
        )->willReturn(
            $this->selectMock
        );
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();

        $storeManager = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStores']);
        $storeManager->expects(
            $this->any()
        )->method(
            'getStores'
        )->with(
            true
        )->willReturn(
            
                [
                    new \Magento\Framework\DataObject(['id' => 0]),
                    new \Magento\Framework\DataObject(['id' => 1])
                ]
            
        );

        /** @var $resource \Magento\Framework\App\ResourceConnection */
        $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $resource->expects($this->any())->method('getTableName')->willReturnArgument(0);
        $resource->expects($this->any())->method('getConnection')->with()->willReturn($connectionMock);
        $eavEntityType = $this->createMock(\Magento\Eav\Model\ResourceModel\Entity\Type::class);

        $relationProcessorMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class
        );

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($resource);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $configMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)->disableOriginalConstructor()->getMock();
        $arguments = [
            'context' => $contextMock,
            'storeManager' => $storeManager,
            'eavEntityType' => $eavEntityType,
        ];
        $helper = new ObjectManager($this);
        $resourceModel = $helper->getObject(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class, $arguments);
        $helper->setBackwardCompatibleProperty(
            $resourceModel,
            'config',
            $configMock
        );

        return [$connectionMock, $resourceModel];
    }

    /**
     * Retrieve eav_attribute table structure
     *
     * @return array
     */
    protected function _describeEavAttribute()
    {
        return require __DIR__ . '/../../../_files/describe_table_eav_attribute.php';
    }
}
