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

namespace Magento\Paypal\Helper\Shortcut;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Paypal\Helper\Shortcut\Factory */
    protected $factory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->factory = $this->objectManagerHelper->getObject(
            'Magento\Paypal\Helper\Shortcut\Factory',
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testCreateDefault()
    {
        $instance = $this->getMockBuilder('Magento\Paypal\Helper\Shortcut\ValidatorInterface')->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')->with(Factory::DEFAULT_VALIDATOR)
            ->will($this->returnValue($instance));

        $this->assertInstanceOf(
            'Magento\Paypal\Helper\Shortcut\ValidatorInterface',
            $this->factory->create()
        );
    }

    public function testCreateCheckout()
    {
        $checkoutMock = $this->getMockBuilder('Magento\Checkout\Model\Session')->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $instance = $this->getMockBuilder('Magento\Paypal\Helper\Shortcut\ValidatorInterface')->getMock();

        $this->objectManagerMock->expects($this->once())->method('create')->with(Factory::CHECKOUT_VALIDATOR)
            ->will($this->returnValue($instance));

        $this->assertInstanceOf(
            'Magento\Paypal\Helper\Shortcut\ValidatorInterface',
            $this->factory->create($checkoutMock)
        );
    }
}
