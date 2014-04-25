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
namespace Magento\Log\Model;

class VisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Log\Model\Visitor
     */
    protected $_model;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject('Magento\Log\Model\Visitor');
    }

    public function testBindCustomerLogin()
    {
        $customer = new \Magento\Framework\Object(['id' => '1']);
        $observer = new \Magento\Framework\Object([
            'event' => new \Magento\Framework\Object(['customer' => $customer])
        ]);

        $this->_model->bindCustomerLogin($observer);
        $this->assertTrue($this->_model->getDoCustomerLogin());
        $this->assertEquals($customer->getId(), $this->_model->getCustomerId());

        $this->_model->unsetData();
        $this->_model->setCustomerId('2');
        $this->_model->bindCustomerLogin($observer);
        $this->assertNull($this->_model->getDoCustomerLogin());
        $this->assertEquals('2', $this->_model->getCustomerId());
    }

    public function testBindCustomerLogout()
    {
        $observer = new \Magento\Framework\Object();

        $this->_model->setCustomerId('1');
        $this->_model->bindCustomerLogout($observer);
        $this->assertTrue($this->_model->getDoCustomerLogout());

        $this->_model->unsetData();
        $this->_model->bindCustomerLogout($observer);
        $this->assertNull($this->_model->getDoCustomerLogout());
    }
}
