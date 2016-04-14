<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Grid\Massaction;

class ItemsUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Sales\Model\Order\Grid\Massaction\ItemsUpdater
     */
    protected $itemUpdater;

    /**
     * @var \Magento\Framework\Authorization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    protected function setUp()
    {
        $this->authorizationMock = $this->getMock('Magento\Framework\Authorization', [], [], '', false);
        $this->itemUpdater = new \Magento\Sales\Model\Order\Grid\Massaction\ItemsUpdater(
            $this->authorizationMock
        );
    }

    public function testUpdate()
    {
        $arguments =[
            'cancel_order' => null,
            'hold_order' => null,
            'unhold_order' => null,
            'other' => null
        ];
        $this->authorizationMock->expects($this->exactly(3))
            ->method('isAllowed')
            ->willReturnMap(
                [
                ['Magento_Sales::cancel', null, false],
                ['Magento_Sales::hold', null, false],
                ['Magento_Sales::unhold', null, false],

                ]
            );
        $this->assertEquals(['other' => null], $this->itemUpdater->update($arguments));
    }
}
