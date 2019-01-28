<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\GiftMessage\Observer\SalesEventQuoteMerge;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;

/**
 *  SalesEventQuoteMergeTest
 */
class SalesEventQuoteMergeTest extends \PHPUnit\Framework\TestCase
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
        $sourceQuoteMock = $this->createPartialMock(Quote::class, ['getGiftMessageId']);
        $sourceQuoteMock->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn($giftMessageId);

        $targetQuoteMock = $this->createPartialMock(Quote::class, ['setGiftMessageId']);

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
