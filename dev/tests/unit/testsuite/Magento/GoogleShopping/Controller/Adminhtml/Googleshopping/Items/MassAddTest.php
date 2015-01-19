<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class MassAddTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items\MassAdd */
    protected $controller;

    /**
     * @var \Magento\GoogleShopping\Model\Flag
     */
    protected $flag;

    /**
     * @var array
     */
    protected $controllerArguments;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $notificationInterface;

    protected function setUp()
    {
        $this->notificationInterface = $this->getMock('Magento\Framework\Notification\NotifierInterface');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controllerArguments = $this->objectManagerHelper->getConstructArguments(
            'Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items\MassAdd',
            [
                'notifier' => $this->notificationInterface
            ]
        );
        $this->flag = $this->getMockBuilder('Magento\GoogleShopping\Model\Flag')->disableOriginalConstructor()
            ->setMethods(['loadSelf', '__sleep', '__wakeup', 'isLocked', 'lock', 'unlock'])->getMock();
        $this->flag->expects($this->once())->method('loadSelf')->will($this->returnSelf());
        $this->flag->expects($this->once())->method('isLocked')->will($this->returnValue(false));

        $store = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()
                ->setMethods(['getId', '__sleep', '__wakeup'])->getMock();
        $store->expects($this->exactly(2))->method('getId')->will($this->returnValue(1));

        $storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->controllerArguments['context']->getObjectManager()
            ->expects($this->at(0))->method('get')->with('Magento\GoogleShopping\Model\Flag')
            ->will($this->returnValue($this->flag));
        $this->controllerArguments['context']->getObjectManager()
            ->expects($this->at(1))->method('get')->with('Magento\Store\Model\StoreManagerInterface')
            ->will($this->returnValue($storeManager));

        $this->controller = $this->objectManagerHelper->getObject(
            'Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items\MassAdd',
            $this->controllerArguments
        );
    }

    public function testExecuteWithException()
    {
        $this->flag->expects($this->once())->method('lock')
            ->will($this->throwException(new \Exception('Test exception')));

        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $this->controllerArguments['context']->getObjectManager()
            ->expects($this->at(2))->method('get')->with('Psr\Log\LoggerInterface')
            ->will($this->returnValue($logger));

        $this->controller->execute();
    }

    public function testExecute()
    {
        $massOperations = $this->getMockBuilder('Magento\GoogleShopping\Model\MassOperations')
            ->disableOriginalConstructor()->setMethods(['setFlag', 'addProducts'])->getMock();
        $massOperations->expects($this->once())->method('setFlag')->will($this->returnSelf());
        $massOperations->expects($this->once())->method('addProducts')->will($this->returnSelf());

        $this->controllerArguments['context']->getObjectManager()->expects($this->once())->method('create')
            ->with('Magento\GoogleShopping\Model\MassOperations')
            ->will($this->returnValue($massOperations));

        $this->controller->execute();
    }
}
