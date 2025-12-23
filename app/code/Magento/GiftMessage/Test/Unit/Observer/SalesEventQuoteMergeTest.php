<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GiftMessage\Observer\SalesEventQuoteMerge;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SalesEventQuoteMergeTest extends TestCase
{
    use MockCreationTrait;

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
     * @param null|int $giftMessageId
     *
     * @return void
     */
    #[DataProvider('dataProviderGiftMessageId')]
    public function testExecute($giftMessageId): void
    {
        $sourceQuoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['getGiftMessageId']
        );
        $sourceQuoteMock->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn($giftMessageId);

        $targetQuoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['setGiftMessageId']
        );

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
    public static function dataProviderGiftMessageId(): array
    {
        return [
            [null],
            [1]
        ];
    }
}
