<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\Collection;

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_model = null;

    protected function setUp()
    {
        $resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\ResourceConnection::class);
        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Model\ResourceModel\Db\Context::class,
            ['resource' => $resourceModel]
        );

        $resource = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [$context],
            '',
            true,
            true,
            true,
            ['getMainTable', 'getIdFieldName']
        );

        $resource->expects(
            $this->any()
        )->method(
            'getMainTable'
        )->will(
            $this->returnValue($resource->getTable('store_website'))
        );
        $resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('website_id'));

        $fetchStrategy = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );

        $eventManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Event\ManagerInterface::class
        );

        $entityFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Data\Collection\EntityFactory::class
        );
        $logger = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Psr\Log\LoggerInterface::class);

        $this->_model = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class,
            [$entityFactory, $logger, $fetchStrategy, $eventManager, null, $resource]
        );
    }

    public function testGetAllIds()
    {
        $allIds = $this->_model->getAllIds();
        sort($allIds);
        $this->assertEquals(['0', '1'], $allIds);
    }

    public function testGetAllIdsWithBind()
    {
        $this->_model->getSelect()->where('code = :code');
        $this->_model->addBindParam('code', 'admin');
        $this->assertEquals(['0'], $this->_model->getAllIds());
    }

    /**
     * Check add field to select doesn't remove expression field from select.
     *
     * @return void
     */
    public function testAddExpressionFieldToSelectWithAdditionalFields()
    {
        $expectedColumns = ['code', 'test_field'];
        $actualColumns = [];

        $testExpression = new \Zend_Db_Expr('(sort_order + group_id)');
        $this->_model->addExpressionFieldToSelect('test_field', $testExpression, ['sort_order', 'group_id']);
        $this->_model->addFieldToSelect('code', 'code');
        $columns = $this->_model->getSelect()->getPart(\Magento\Framework\DB\Select::COLUMNS);
        foreach ($columns as $columnEntry) {
            $actualColumns[] = $columnEntry[2];
        }

        $this->assertEquals($expectedColumns, $actualColumns);
    }

    /**
     * Check add expression field doesn't remove all fields from select.
     *
     * @return void
     */
    public function testAddExpressionFieldToSelectWithoutAdditionalFields()
    {
        $expectedColumns = ['*', 'test_field'];

        $testExpression = new \Zend_Db_Expr('(sort_order + group_id)');
        $this->_model->addExpressionFieldToSelect('test_field', $testExpression, ['sort_order', 'group_id']);
        $columns = $this->_model->getSelect()->getPart(\Magento\Framework\DB\Select::COLUMNS);
        $actualColumns = [$columns[0][1], $columns[1][2]];

        $this->assertEquals($expectedColumns, $actualColumns);
    }
}
