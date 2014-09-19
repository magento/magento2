<?php
/** 
 * 
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
 
namespace Magento\RecurringPayment\Model\Observer;
 
class UnserializeProductRecurringPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UnserializeProductRecurringPayment
     */
    protected $model;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;
    
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->eventMock = $this->getMock('Magento\Framework\Event', ['getCollection', '__wakeup'], [], '', false);
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product',
            [
                'getIsRecurring',
                'getRecurringPayment',
                'setRecurringPayment',
                '__wakeup',
                '__sleep'
            ],
            [],
            '',
            false);
        $this->collectionMock = $objectManager->getCollectionMock(
            'Magento\Catalog\Model\Resource\Product\Collection',
            [$this->productMock]);

        $this->model = new UnserializeProductRecurringPayment();
    }

    public function testExecute()
    {
        $payment = new \stdClass();
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())
            ->method('getCollection')->will($this->returnValue($this->collectionMock));
        $this->productMock
            ->expects($this->once())
            ->method('getIsRecurring')
            ->will($this->returnValue(true));
        $this->productMock->expects($this->once())
            ->method('getRecurringPayment')
            ->will($this->returnValue(serialize($payment)));
        $this->productMock->expects($this->once())
            ->method('setRecurringPayment')->with($payment);

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithoutRecurring()
    {
        $payment = new \stdClass();
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())
            ->method('getCollection')->will($this->returnValue($this->collectionMock));
        $this->productMock
            ->expects($this->once())
            ->method('getIsRecurring')
            ->will($this->returnValue(false));
        $this->productMock->expects($this->once())
            ->method('getRecurringPayment')
            ->will($this->returnValue(serialize($payment)));
        $this->productMock->expects($this->never())
            ->method('setRecurringPayment');

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithoutPayment()
    {
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())
            ->method('getCollection')->will($this->returnValue($this->collectionMock));
        $this->productMock
            ->expects($this->once())
            ->method('getIsRecurring')
            ->will($this->returnValue(true));
        $this->productMock->expects($this->once())
            ->method('getRecurringPayment')
            ->will($this->returnValue(null));
        $this->productMock->expects($this->never())
            ->method('setRecurringPayment');

        $this->model->execute($this->observerMock);
    }
}
