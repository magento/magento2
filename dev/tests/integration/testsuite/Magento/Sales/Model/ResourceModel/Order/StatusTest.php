<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class StatusTest
 */
class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status
     */
    protected $resourceModel;

    /**
     * Test setUp
     */
    public function setUp()
    {
        $this->resourceModel = Bootstrap::getObjectManager()
            ->create(
                'Magento\Sales\Model\ResourceModel\Order\Status',
                [
                    'data' => ['status' => 'fake_status']
                ]
            );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/assign_status_to_state.php
     */
    public function testUnassignState()
    {
        $this->resourceModel->unassignState('fake_status_do_not_use_it', 'fake_state_do_not_use_it');
        $this->assertTrue(true);
        $this->assertFalse((bool)
            $this->resourceModel->getConnection()->fetchOne($this->resourceModel->getConnection()->select()
            ->from($this->resourceModel->getTable('sales_order_status_state'), [new \Zend_Db_Expr(1)])
            ->where('status = ?', 'fake_status_do_not_use_it')
            ->where('state = ?', 'fake_state_do_not_use_it')));
    }
}
