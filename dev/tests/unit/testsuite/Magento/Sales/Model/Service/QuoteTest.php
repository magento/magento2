<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class QuoteTest
 */
class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Service\Quote
     */
    protected $quoteService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);
        $convertFactory = $this->getMock('Magento\Sales\Model\Convert\QuoteFactory', ['create'], [], '', false);
        $convertFactory->expects($this->once())
            ->method('create');
        $this->quoteMock = $this->getMock(
            'Magento\Sales\Model\Quote',
            ['getAllVisibleItems', 'setIsActive'],
            [],
            '',
            false
        );
        $this->quoteService = $objectManager->getObject(
            'Magento\Sales\Model\Service\Quote',
            ['quote' => $this->quoteMock, 'convertQuoteFactory' => $convertFactory]
        );
    }

    public function testSubmitAllWithDataObject()
    {
        $this->quoteMock->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn(false);
        $this->quoteMock->expects($this->once())
            ->method('setIsActive');
        $this->quoteService->submitAllWithDataObject();
    }
}
