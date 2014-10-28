<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Resource\Attribute;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Resource\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Core\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $entityTypeMock;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Zend_Db_Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->fetchStrategyMock = $this->getMock('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);
        $this->entityTypeMock = $this->getMock('Magento\Eav\Model\Entity\Type', array('__wakeup'), array(), '', false);
        $this->entityTypeMock->setAdditionalAttributeTable('some_extra_table');
        $this->eavConfigMock->expects($this->any())
            ->method('getEntityType')
            ->will($this->returnValue($this->entityTypeMock));

        $this->storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnSelf());

        $adapter = $this->getMockForAbstractClass('Zend_Db_Adapter_Abstract', array(), '', false);

        $this->selectMock = $this->getMock('Zend_Db_Select', null, array($adapter));

        $this->connectionMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            array('select','describeTable', 'quoteIdentifier', '_connect', '_quote'),
            array(),
            '',
            false);

        $this->resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            array(),
            '',
            false,
            true,
            true,
            array('__wakeup', 'getReadConnection', 'getMainTable', 'getTable')
        );

        $this->connectionMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->selectMock));
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnvalueMap(
                array(
                    array(
                        'some_main_table',
                        null,
                        array(
                            'col1' => array(),
                            'col2' => array(),
                        )
                    ),
                    array(
                        'some_extra_table',
                        null,
                        array(
                            'col2' => array(),
                            'col3' => array(),
                        )
                    ),
                    array(
                        null,
                        null,
                        array(
                            'col2' => array(),
                            'col3' => array(),
                            'col4' => array(),
                        )
                    ),
                )
            ));
        $this->connectionMock->expects($this->any())
            ->method('_quote')
            ->will($this->returnArgument(0));

        $this->resourceMock->expects($this->any())
            ->method('getReadConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->resourceMock->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue('some_main_table'));
        $this->resourceMock->expects($this->any())
            ->method('getTable')
            ->will(
                $this->returnValue('some_extra_table')
            );
    }

    /**
     * Test that Magento\Eav\Model\Resource\Attribute\Collection::_initSelect sets expressions
     * that can be properly quoted by Zend_Db_Expr::quoteIdentifier
     *
     * @dataProvider initSelectDataProvider
     */
    public function testInitSelect($column, $value, $expected)
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $helper->getObject('\Magento\Customer\Model\Resource\Attribute\Collection',
            array(
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->eventManagerMock,
                'eavConfig' => $this->eavConfigMock,
                'storeManager' => $this->storeManagerMock,
                'connection' => $this->connectionMock,
                'resource' => $this->resourceMock
            )
        );

        $this->model->addFieldToFilter($column, $value);
        $this->assertEquals($expected, $this->model->getSelectCountSql()->assemble());
    }

    public function initSelectDataProvider()
    {
        return array(
            'main_table_expression' => array(
                'col2', '1',
                'SELECT COUNT(DISTINCT main_table.attribute_id) FROM "some_main_table" AS "main_table"' . "\n"
                . ' INNER JOIN "some_extra_table" AS "additional_table"'
                . ' ON additional_table.attribute_id = main_table.attribute_id' . "\n"
                . ' LEFT JOIN "some_extra_table" AS "scope_table"'
                . ' ON scope_table.attribute_id = main_table.attribute_id'
                . ' AND scope_table.website_id = :scope_website_id'
                . ' WHERE (main_table.entity_type_id = :mt_entity_type_id)'
                . ' AND (IF(main_table.col2 IS NULL, scope_table.col2, main_table.col2) = 1)'
            ),
            'additional_table_expression' => array(
                'col3', '2',
                'SELECT COUNT(DISTINCT main_table.attribute_id) FROM "some_main_table" AS "main_table"' . "\n"
                . ' INNER JOIN "some_extra_table" AS "additional_table"'
                . ' ON additional_table.attribute_id = main_table.attribute_id'. "\n"
                . ' LEFT JOIN "some_extra_table" AS "scope_table"'
                . ' ON scope_table.attribute_id = main_table.attribute_id'
                . ' AND scope_table.website_id = :scope_website_id'
                . ' WHERE (main_table.entity_type_id = :mt_entity_type_id)'
                . ' AND (IF(additional_table.col3 IS NULL, scope_table.col3, additional_table.col3) = 2)'
            )
        );
    }
}
