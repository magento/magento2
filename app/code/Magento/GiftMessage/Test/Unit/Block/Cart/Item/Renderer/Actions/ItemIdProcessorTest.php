<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\GiftMessage\Block\Cart\Item\Renderer\Actions\ItemIdProcessor;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemIdProcessorTest extends TestCase
{
    /** @var ItemIdProcessor */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new ItemIdProcessor();
    }

    /**
     * @param int $itemId
     * @param array $jsLayout
     * @param array $result
     */
    #[DataProvider('dataProviderProcess')]
    public function testProcess($itemId, array $jsLayout, array $result)
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);

        $this->assertEquals($result, $this->model->process($jsLayout, $itemMock));
    }

    /**
     * @return array
     */
    public static function dataProviderProcess()
    {
        return [
            [
                12,
                ['components' => []],
                ['components' => []],
            ],
            [
                21,
                ['components' => ['giftOptionsCartItem' => []]],
                ['components' => ['giftOptionsCartItem-21' => ['config' => ['itemId' => 21]]]],
            ],
            [
                23,
                ['components' => ['giftOptionsCartItem' => ['config' => ['key' => 'value']]]],
                ['components' => ['giftOptionsCartItem-23' => ['config' => ['key' => 'value', 'itemId' => 23]]]],
            ],
            [
                23,
                ['components' => ['giftOptionsCartItem' => ['config' => ['key' => 'value'], 'key2' => 'value2']]],
                [
                    'components' => [
                        'giftOptionsCartItem-23' => [
                            'config' => ['key' => 'value', 'itemId' => 23], 'key2' => 'value2'
                        ]
                    ]
                ],
            ],
        ];
    }
}
