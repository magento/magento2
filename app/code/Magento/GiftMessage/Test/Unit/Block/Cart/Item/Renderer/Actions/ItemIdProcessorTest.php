<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\GiftMessage\Block\Cart\Item\Renderer\Actions\ItemIdProcessor;
use Magento\Quote\Model\Quote\Item;

class ItemIdProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ItemIdProcessor */
    protected $model;

    protected function setUp()
    {
        $this->model = new ItemIdProcessor();
    }

    /**
     * @param int $itemId
     * @param array $jsLayout
     * @param array $result
     * @dataProvider dataProviderProcess
     */
    public function testProcess($itemId, array $jsLayout, array $result)
    {
        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getId')
            ->willReturn($itemId);

        $this->assertEquals($result, $this->model->process($jsLayout, $itemMock));
    }

    public function dataProviderProcess()
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
