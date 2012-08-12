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
 * @category    Magento
 * @package     Mage_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Sales_Model_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Mage/Sales/_files/order.php
     */
    public function testAfterCommitCallbackOrderGrid()
    {
        $collection = new Mage_Sales_Model_Resource_Order_Grid_Collection;
        $this->assertEquals(1, $collection->count());
        foreach ($collection as $order) {
            $this->assertInstanceOf('Mage_Sales_Model_Order', $order);
            $this->assertEquals('100000001', $order->getIncrementId());
        }
    }

    public function testAfterCommitCallbackOrderGridNotInvoked()
    {
        $adapter = Mage::getResourceSingleton('Mage_Core_Model_Resource')->getConnection('write');
        $this->assertEquals(0, $adapter->getTransactionLevel(), 'This test must be outside a transaction.');

        $localOrderModel = new Mage_Sales_Model_Order;
        $resource = $localOrderModel->getResource();
        $resource->beginTransaction();
        try {
            /** @var $order Mage_Sales_Model_Order */
            require __DIR__ . '/../_files/order.php';
            $collection = new Mage_Sales_Model_Resource_Order_Grid_Collection;
            $this->assertEquals(0, $collection->count());
            $resource->rollBack();
        } catch (Exception $e) {
            $resource->rollBack();
            throw $e;
        }
    }
}
