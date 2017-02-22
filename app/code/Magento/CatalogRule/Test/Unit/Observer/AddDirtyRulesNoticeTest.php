<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AddDirtyRulesNoticeTest
 */
class AddDirtyRulesNoticeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Observer\AddDirtyRulesNotice
     */
    private $observer;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    protected function setUp()
    {
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManager($this);
        $this->observer = $objectManagerHelper->getObject(
            \Magento\CatalogRule\Observer\AddDirtyRulesNotice::class,
            [
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    public function testExecute()
    {
        $message = "test";
        $flagMock = $this->getMockBuilder(\Magento\CatalogRule\Model\Flag::class)
            ->setMethods(['getState'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserverMock->expects($this->at(0))->method('getData')->with('dirty_rules')->willReturn($flagMock);
        $flagMock->expects($this->once())->method('getState')->willReturn(1);
        $eventObserverMock->expects($this->at(1))->method('getData')->with('message')->willReturn($message);
        $this->messageManagerMock->expects($this->once())->method('addNotice')->with($message);
        $this->observer->execute($eventObserverMock);
    }
}
