<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Quote\Model\Quote;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Totals block test
 */
class TotalsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Totals
     */
    protected $totals;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var SessionQuote|MockObject
     */
    protected $sessionQuoteMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        // Initialize ObjectManager to avoid "ObjectManager isn't initialized" errors
        $this->objectManager->prepareObjectManager();

        $this->quoteMock = $this->createPartialMock(Quote::class, ['getCustomerNoteNotify']);
        $this->sessionQuoteMock = $this->createMock(SessionQuote::class);

        $this->sessionQuoteMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->totals = $this->objectManager->getObject(
            Totals::class,
            [
                'sessionQuote' => $this->sessionQuoteMock
            ]
        );
    }

    /**
     * @param mixed $customerNoteNotify
     * @param bool $expectedResult
     */
    #[DataProvider('getNoteNotifyDataProvider')]
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
    public static function getNoteNotifyDataProvider()
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
