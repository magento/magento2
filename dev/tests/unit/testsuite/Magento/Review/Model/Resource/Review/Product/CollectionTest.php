<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Model\Resource\Review\Product;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readAdapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbSelect;

    public function setUp()
    {
        $attribute = $this->getMock('\Magento\Eav\Model\Entity\Attribute\AbstractAttribute', null, [], '', false);
        $eavConfig = $this->getMock('\Magento\Eav\Model\Config', ['getCollectionAttribute'], [], '', false);
        $eavConfig->expects($this->any())->method('getCollectionAttribute')->will($this->returnValue($attribute));
        $this->dbSelect = $this->getMock('Magento\Framework\DB\Select', ['where', 'from', 'join'], [], '', false);
        $this->dbSelect->expects($this->any())->method('from')->will($this->returnSelf());
        $this->dbSelect->expects($this->any())->method('join')->will($this->returnSelf());
        $this->readAdapter = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['prepareSqlCondition', 'select'],
            [],
            '',
            false
        );
        $this->readAdapter->expects($this->once())->method('select')->will($this->returnValue($this->dbSelect));
        $entity = $this->getMock(
            'Magento\Catalog\Model\Resource\Product',
            ['getReadConnection', 'getTable', 'getDefaultAttributes', 'getEntityTable', 'getEntityType', 'getType'],
            [],
            '',
            false
        );
        $entity->expects($this->once())->method('getReadConnection')->will($this->returnValue($this->readAdapter));
        $entity->expects($this->any())->method('getTable')->will($this->returnValue('table'));
        $entity->expects($this->any())->method('getEntityTable')->will($this->returnValue('table'));
        $entity->expects($this->any())->method('getDefaultAttributes')->will($this->returnValue([1 => 1]));
        $entity->expects($this->any())->method('getType')->will($this->returnValue('type'));
        $entity->expects($this->any())->method('getEntityType')->will($this->returnValue('type'));
        $universalFactory = $this->getMock('\Magento\Framework\Validator\UniversalFactory', ['create'], [], '', false);
        $universalFactory->expects($this->any())->method('create')->will($this->returnValue($entity));
        $store = $this->getMock('\Magento\Store\Model\Store', ['getId'], [], '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->model = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject(
                '\Magento\Review\Model\Resource\Review\Product\Collection',
                [
                    'universalFactory' => $universalFactory,
                    'storeManager' => $storeManager,
                    'eavConfig' => $eavConfig,
                ]
            );
    }

    /**
     * @dataProvider addAttributeToFilterDataProvider
     * @param $attribute
     * @param $condition
     */
    public function testAddAttributeToFilter($attribute, $condition)
    {
        $conditionSqlQuery = 'sqlQuery';
        switch ($attribute) {
            case 'rt.review_id':
            case 'rt.created_at':
            case 'rt.status_id':
            case 'rdt.title':
            case 'rdt.nickname':
            case 'rdt.detail':
                $this->readAdapter
                    ->expects($this->once())
                    ->method('prepareSqlCondition')
                    ->with($attribute, $condition)
                    ->will($this->returnValue($conditionSqlQuery));
                $this->dbSelect
                    ->expects($this->once())
                    ->method('where')
                    ->with($conditionSqlQuery)
                    ->will($this->returnSelf());
                break;
            case 'stores':
                break;
            case 'type':
                switch ($condition) {
                    case 1:
                        $this->readAdapter
                            ->expects($this->at(0))
                            ->method('prepareSqlCondition')
                            ->with('rdt.customer_id', ['is' => new \Zend_Db_Expr('NULL')])
                            ->will($this->returnValue($conditionSqlQuery));
                        $this->readAdapter
                            ->expects($this->at(1))->method('prepareSqlCondition')
                            ->with('rdt.store_id', ['eq' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                            ->will($this->returnValue($conditionSqlQuery));
                        $this->dbSelect
                            ->expects($this->once())
                            ->method('where')
                            ->with($conditionSqlQuery . ' AND ' . $conditionSqlQuery)
                            ->will($this->returnSelf());
                        break;
                    case 2:
                        $this->readAdapter
                            ->expects($this->at(0))
                            ->method('prepareSqlCondition')
                            ->with('rdt.customer_id', ['gt' => 0])
                            ->will($this->returnValue($conditionSqlQuery));
                        $this->dbSelect
                            ->expects($this->once())
                            ->method('where')
                            ->with($conditionSqlQuery)
                            ->will($this->returnSelf());
                        break;
                    default:
                        $this->readAdapter
                            ->expects($this->at(0))
                            ->method('prepareSqlCondition')
                            ->with('rdt.customer_id', ['is' => new \Zend_Db_Expr('NULL')])
                            ->will($this->returnValue($conditionSqlQuery));
                        $this->readAdapter
                            ->expects($this->at(1))
                            ->method('prepareSqlCondition')
                            ->with('rdt.store_id', ['neq' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                            ->will($this->returnValue($conditionSqlQuery));
                        $this->dbSelect
                            ->expects($this->once())
                            ->method('where')
                            ->with($conditionSqlQuery . ' AND ' . $conditionSqlQuery)
                            ->will($this->returnSelf());
                        break;
                }
                break;
        }
        $this->model->addAttributeToFilter($attribute, $condition);
    }

    /**
     * @return array
     */
    public function addAttributeToFilterDataProvider()
    {
        return [
            ['rt.review_id', ['eq' => 'value']],
            ['rt.created_at', ['eq' => 'value']],
            ['rt.status_id', ['eq' => 'value']],
            ['rdt.title', ['eq' => 'value']],
            ['rdt.nickname', ['eq' => 'value']],
            ['rdt.detail', ['eq' => 'value']],
            ['stores', ['eq' => 'value']],
            ['type', 1],
            ['type', 2],
            ['type', null],
        ];
    }
}
