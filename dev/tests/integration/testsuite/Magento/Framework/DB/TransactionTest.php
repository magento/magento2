<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\DB\Transaction');
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testSaveDelete()
    {
        $first = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $first->setData(
            ['website_id' => 1, 'name' => 'test 1', 'root_category_id' => 1, 'default_store_id' => 1]
        );

        $second = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $second->setData(
            ['website_id' => 1, 'name' => 'test 2', 'root_category_id' => 1, 'default_store_id' => 1]
        );

        $first->save();
        $this->_model->addObject($first)->addObject($second, 'second');
        $this->_model->save();
        $this->assertNotEmpty($first->getId());
        $this->assertNotEmpty($second->getId());

        $this->_model->delete();

        $test = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $test->load($first->getId());
        $this->assertEmpty($test->getId());
    }
}
