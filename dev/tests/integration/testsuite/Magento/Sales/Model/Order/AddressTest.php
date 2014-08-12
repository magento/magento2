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
namespace Magento\Sales\Model\Order;

use Magento\TestFramework\Helper\Bootstrap;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /** @var Address */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Address');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testSave()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        /** @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface $customerAddressService */
        $customerAddressService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerAddressServiceInterface'
        );
        $order->loadByIncrementId('100000001');
        $this->_model->setOrder($order);
        $this->_model->setData($customerAddressService->getAddress(1)->__toArray());
        $this->_model->setEmail('co@co.co');
        $this->_model->setAddressType('billing');
        $this->_model->setRegionId(1);
        $this->_model->save();
        $this->assertEquals($order->getId(), $this->_model->getParentId());
    }
}
