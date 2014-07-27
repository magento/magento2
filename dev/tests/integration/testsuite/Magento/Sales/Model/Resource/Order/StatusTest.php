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

namespace Magento\Sales\Model\Resource\Order;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class StatusTest
 */
class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Status
     */
    protected $resourceModel;

    /**
     * Test setUp
     */
    public function setUp()
    {
        $this->resourceModel = Bootstrap::getObjectManager()
            ->create(
                'Magento\Sales\Model\Resource\Order\Status',
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
            $this->resourceModel->getReadConnection()->fetchOne($this->resourceModel->getReadConnection()->select()
            ->from($this->resourceModel->getTable('sales_order_status_state'), [new \Zend_Db_Expr(1)])
            ->where('status = ?', 'fake_status_do_not_use_it')
            ->where('state = ?', 'fake_state_do_not_use_it')));
    }
}
