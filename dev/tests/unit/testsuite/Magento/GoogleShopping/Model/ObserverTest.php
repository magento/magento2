<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GoogleShopping\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\GoogleShopping\Model\Observer */
    protected $observer;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $collectionFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $massOperationsFactory;

    /** @var \Magento\Framework\Notification\NotifierInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $notificationInterface;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigInterface;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterface;

    /** @var \Magento\GoogleShopping\Model\Flag|\PHPUnit_Framework_MockObject_MockObject */
    protected $flag;

    protected function setUp()
    {
        $this->collectionFactory = $this->getMock('Magento\GoogleShopping\Model\Resource\Item\CollectionFactory');
        $this->massOperationsFactory = $this->getMock('Magento\GoogleShopping\Model\MassOperationsFactory');
        $this->notificationInterface = $this->getMock('Magento\Framework\Notification\NotifierInterface');
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->managerInterface = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $this->flag = $this->getMockBuilder('Magento\GoogleShopping\Model\Flag')
            ->setMethods(['loadSelf', 'isExpired', 'unlock', '__sleep', '__wakeup'])
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->observer = $this->objectManagerHelper->getObject(
            'Magento\GoogleShopping\Model\Observer',
            [
                'collectionFactory' => $this->collectionFactory,
                'operationsFactory' => $this->massOperationsFactory,
                'notifier' => $this->notificationInterface,
                'scopeConfig' => $this->scopeConfigInterface,
                'messageManager' => $this->managerInterface,
                'flag' => $this->flag
            ]
        );
    }

    public function testCheckSynchronizationOperations()
    {
        $this->flag->expects($this->once())->method('loadSelf')->will($this->returnSelf());
        $this->flag->expects($this->once())->method('isExpired')->will($this->returnValue(true));
        $observer = $this->objectManagerHelper->getObject('Magento\Framework\Event\Observer');
        $this->notificationInterface->expects($this->once())->method('addMajor')
            ->with(
                'Google Shopping operation has expired.',
                'One or more google shopping synchronization operations failed because of timeout.'
            )->will($this->returnSelf());
        $this->observer->checkSynchronizationOperations($observer);
    }
}
