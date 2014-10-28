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

namespace Magento\Payment\Model;

class MethodListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $specificationFactoryMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->paymentHelperMock = $this->getMock('\Magento\Payment\Helper\Data', [], [], '', false);
        $this->specificationFactoryMock = $this->getMock(
            '\Magento\Payment\Model\Checks\SpecificationFactory', [], [], '', false
        );
        $this->methodList = $this->objectManager->getObject(
            '\Magento\Payment\Model\MethodList',
            [
                'paymentHelper' => $this->paymentHelperMock,
                'specificationFactory' => $this->specificationFactoryMock
            ]
        );
    }

    public function testGetAvailableMethods()
    {
        $storeId = 1;
        $quoteMock = $this->getMock('\Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->will($this->returnValue($this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false)));

        $methodMock = $this->getMock('Magento\Payment\Model\Method\AbstractMethod', ['setInfoInstance'], [], '', false);

        $compositeMock = $this->getMock('\Magento\Payment\Model\Checks\Composite', [], [], '', false);
        $compositeMock->expects($this->atLeastOnce())
            ->method('isApplicable')
            ->with($methodMock, $quoteMock)
            ->will($this->returnValue(true));

        $this->specificationFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($compositeMock));

        $storeMethods = [$methodMock];

        $this->paymentHelperMock->expects($this->once())
            ->method('getStoreMethods')
            ->with($storeId, $quoteMock)
            ->will($this->returnValue($storeMethods));

        $methodMock->expects($this->atLeastOnce())
            ->method('setInfoInstance')
            ->with($this->getMock('\Magento\Sales\Model\Quote\Payment', [], [], '', false))
            ->will($this->returnSelf());

        $this->assertEquals([$methodMock], $this->methodList->getAvailableMethods($quoteMock));
    }
}
 