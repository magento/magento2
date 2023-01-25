<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GiftMessage\Observer\SalesEventQuoteMerge;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class SalesEventQuoteMergeTest extends TestCase
{

    /**
     * @var SalesEventQuoteMerge
     */
    private $salesEventQuoteMerge;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManger = new ObjectManager($this);
        $this->salesEventQuoteMerge = $objectManger->getObject(SalesEventQuoteMerge::class);
    }

    /**
     * @dataProvider dataProviderGiftMessageId
     *
     * @param null|int $giftMessageId
     *
     * @return void
     */
    public function testExecute($giftMessageId): void
    {
        $sourceQuoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getGiftMessageId'])
            ->disableOriginalConstructor()
            ->getMock();
        $sourceQuoteMock->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn($giftMessageId);

        $targetQuoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setGiftMessageId'])
            ->disableOriginalConstructor()
            ->getMock();

        if ($giftMessageId) {
            $targetQuoteMock->expects($this->once())
                ->method('setGiftMessageId');
        } else {
            $targetQuoteMock->expects($this->never())
                ->method('setGiftMessageId');
        }

        $observer = $this->createMock(Observer::class);
        $observer->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap([
                ['quote', null, $targetQuoteMock],
                ['source', null, $sourceQuoteMock]
            ]);

        $this->salesEventQuoteMerge->execute($observer);
    }

    /**
     * @return array
     */
    public function dataProviderGiftMessageId(): array
    {
        return [
            [null],
            [1]
        ];
    }
}
