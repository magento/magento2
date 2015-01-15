<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Totals block test
 */
class TotalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Create\Totals
     */
    protected $totals;

    /**
     * @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionQuoteMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote', ['getCustomerNoteNotify'], [], '', false
        );
        $this->sessionQuoteMock = $this->getMock(
            '\Magento\Backend\Model\Session\Quote', [], [], '', false
        );

        $this->sessionQuoteMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->totals = $this->objectManager->getObject(
            '\Magento\Sales\Block\Adminhtml\Order\Create\Totals',
            [
                'sessionQuote' => $this->sessionQuoteMock
            ]
        );
    }

    /**
     * @param mixed $customerNoteNotify
     * @param bool $expectedResult
     * @dataProvider getNoteNotifyDataProvider
     */
    public function testGetNoteNotify($customerNoteNotify, $expectedResult)
    {
        $this->quoteMock->expects($this->any())
            ->method('getCustomerNoteNotify')
            ->willReturn($customerNoteNotify);

        $this->assertEquals($expectedResult, $this->totals->getNoteNotify());
    }

    /**
     * @return array
     */
    public function getNoteNotifyDataProvider()
    {
        return [
            [0, false],
            [1, true],
            ['0', false],
            ['1', true],
            [null, true]
        ];
    }
}
