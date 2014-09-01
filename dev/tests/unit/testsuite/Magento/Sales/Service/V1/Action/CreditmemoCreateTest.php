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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class CreditmemoCreateTest
 */
class CreditmemoCreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\CreditmemoCreate
     */
    protected $creditmemoCreate;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    public function setUp()
    {
        $this->creditmemoConverterMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\CreditmemoConverter')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Magento\Framework\Logger')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->creditmemoCreate = new CreditmemoCreate(
            $this->creditmemoConverterMock,
            $this->loggerMock
        );
    }

    public function testInvoke()
    {
        $creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('register');
        $creditmemoMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));
        $creditmemoMock->expects($this->once())
            ->method('isValidGrandTotal')
            ->will($this->returnValue(true));
        $creditmemoDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->creditmemoConverterMock->expects($this->once())
            ->method('getModel')
            ->with($creditmemoDataObjectMock)
            ->will($this->returnValue($creditmemoMock));
        $this->assertTrue($this->creditmemoCreate->invoke($creditmemoDataObjectMock));
    }

    public function testInvokeNotValidTotal()
    {
        $creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('isValidGrandTotal')
            ->will($this->returnValue(false));
        $creditmemoDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->creditmemoConverterMock->expects($this->once())
            ->method('getModel')
            ->with($creditmemoDataObjectMock)
            ->will($this->returnValue($creditmemoMock));
        $this->assertFalse($this->creditmemoCreate->invoke($creditmemoDataObjectMock));
    }

    public function testInvokeNoCreditmemo()
    {
        $creditmemoDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->creditmemoConverterMock->expects($this->once())
            ->method('getModel')
            ->with($creditmemoDataObjectMock)
            ->will($this->returnValue(false));
        $this->assertFalse($this->creditmemoCreate->invoke($creditmemoDataObjectMock));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An error has occurred during creating Creditmemo
     */
    public function testInvokeException()
    {
        $message = 'Can not save Creditmemo';
        $e = new \Exception($message);

        $creditmemoDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerMock->expects($this->once())
            ->method('logException')
            ->with($e);
        $this->creditmemoConverterMock->expects($this->once())
            ->method('getModel')
            ->with($creditmemoDataObjectMock)
            ->will($this->throwException($e));
        $this->creditmemoCreate->invoke($creditmemoDataObjectMock);
    }
}
