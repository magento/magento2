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
 * @package     Magento_Pricing
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Pricing\Price;

/**
 * Test class for \Magento\Pricing\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $model;

    /**
     * @var \Magento\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    public function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Pricing\Price\Factory', array(
            'objectManager' => $this->objectManagerMock
        ));
    }

    public function testCreate()
    {
        $quantity = 2.2;
        $className = 'Magento\Pricing\Price\PriceInterface';
        $priceMock = $this->getMock($className);
        $salableItem = $this->getMock('Magento\Pricing\Object\SaleableInterface');
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['salableItem' => $salableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($priceMock));

        $this->assertEquals($priceMock, $this->model->create($salableItem, $className, $quantity, $arguments));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Magento\Pricing\PriceInfo\Base doesn't implement \Magento\Pricing\Price\PriceInterface
     */
    public function testCreateWithException()
    {
        $quantity = 2.2;
        $className = 'Magento\Pricing\PriceInfo\Base';
        $priceMock = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $salableItem = $this->getMock('Magento\Pricing\Object\SaleableInterface');
        $arguments = [];

        $argumentsResult = array_merge($arguments, ['salableItem' => $salableItem, 'quantity' => $quantity]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($priceMock));

        $this->model->create($salableItem, $className, $quantity, $arguments);
    }
}
