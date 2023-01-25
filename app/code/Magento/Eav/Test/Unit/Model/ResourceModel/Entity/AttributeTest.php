<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\ResourceModel\Entity;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $selectMock;

    /**
     * @var MockObject
     */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $eventManagerMock->expects($this->any())->method('dispatch');
        $this->contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
    }

    /**
     * @covers \Magento\Eav\Model\ResourceModel\Entity\Attribute::_saveOption
     */
    public function testSaveOptionSystemAttribute()
    {
        /** @var MockObject $connectionMock */
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
            'source_model' => Status::class,
            'is_required' => 1,
            'is_user_defined' => 0,
            'is_unique' => 0
        ];

        $objectManagerHelper = new ObjectManager($this);
        /** @var AbstractModel $model */
        $arguments = $objectManagerHelper->getConstructArguments(AbstractModel::class);
        $arguments['data'] = $attributeData;
        $arguments['context'] = $this->contextMock;

        $model = $this->getMockBuilder(AbstractModel::class)
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
        /** @var MockObject $connectionMock */
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
            'source_model' => Table::class,
            'is_required' => 0,
            'is_user_defined' => 1,
            'is_unique' => 0,
        ];

        $objectManagerHelper = new ObjectManager($this);
        /** @var AbstractModel $model */
        $arguments = $objectManagerHelper->getConstructArguments(AbstractModel::class);
        $arguments['data'] = $attributeData;
        $arguments['context'] = $this->contextMock;
        $model = $this->getMockBuilder(AbstractModel::class)
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
        /** @var MockObject $connectionMock */
        /** @var $resourceModel \Magento\Eav\Model\ResourceModel\Entity\Attribute */
        list($connectionMock, $resourceModel) = $this->_prepareResourceModel();

        $objectManagerHelper = new ObjectManager($this);
        /** @var AbstractModel $model */
        $arguments = $objectManagerHelper->getConstructArguments(AbstractModel::class);
        $arguments['context'] = $this->contextMock;
        $model = $this->getMockBuilder(AbstractModel::class)
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
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(
                [
                    'delete',
                    'rollback',
                    'describeTable',
                    'fetchRow',
                    'insert',
                    'lastInsertId',
                    'quote',
                    'update',
                    'beginTransaction',
                    'commit',
                    'select',
                    'getTransactionLevel'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->selectMock = $this->createMock(Select::class);
        $connectionMock->expects(
            $this->any()
        )->method(
            'select'
        )->willReturn(
            $this->selectMock
        );
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();

        $storeManager = $this->createPartialMock(StoreManager::class, ['getStores']);
        $storeManager->expects(
            $this->any()
        )->method(
            'getStores'
        )->with(
            true
        )->willReturn(
            [
                new DataObject(['id' => 0]),
                new DataObject(['id' => 1])
            ]
        );

        /** @var $resource \Magento\Framework\App\ResourceConnection */
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects($this->any())->method('getTableName')->willReturnArgument(0);
        $resource->expects($this->any())->method('getConnection')->with()->willReturn($connectionMock);
        $eavEntityType = $this->createMock(Type::class);

        $relationProcessorMock = $this->createMock(
            ObjectRelationProcessor::class
        );

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($resource);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $arguments = [
            'context' => $contextMock,
            'storeManager' => $storeManager,
            'eavEntityType' => $eavEntityType,
        ];
        $helper = new ObjectManager($this);
        $resourceModel = $helper->getObject(Attribute::class, $arguments);
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
