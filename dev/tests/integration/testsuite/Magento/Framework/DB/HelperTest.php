<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_model;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $_select;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\DB\Helper::class,
            ['modulePrefix' => 'core']
        );
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\ResourceModel\Store\Collection::class
        );
        $this->_select = $collection->getSelect();
    }

    public function testPrepareColumnsList()
    {
        $columns = $this->_model->prepareColumnsList($this->_select);
        $this->assertContains('STORE_ID', array_keys($columns));
    }

    public function testAddGroupConcatColumn()
    {
        $select = (string)$this->_model->addGroupConcatColumn($this->_select, 'test_alias', 'store_id');
        $this->assertStringContainsString('GROUP_CONCAT', $select);
        $this->assertStringContainsString('test_alias', $select);
    }

    public function testGetDateDiff()
    {
        $diff = $this->_model->getDateDiff('2011-01-01', '2011-01-01');
        $this->assertInstanceOf('Zend_Db_Expr', $diff);
        $this->assertStringContainsString('TIMESTAMPDIFF(DAY', (string)$diff);
    }

    public function testAddLikeEscape()
    {
        $value = $this->_model->addLikeEscape('test');
        $this->assertInstanceOf('Zend_Db_Expr', $value);
        $this->assertStringContainsString('test', (string)$value);
    }
}
