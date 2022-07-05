<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Context;
use Magento\QuoteGraphQl\Model\Resolver\Discounts;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see Discounts
 */
class DiscountsTest extends TestCase
{
    /**
     * @var Discounts
     */
    private Discounts $discounts;

    /**
     * @var Field|MockObject
     */
    private Field $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo $resolveInfoMock;

    /**
     * @var Context|MockObject
     */
    private Context $contextMock;

    protected function setUp(): void
    {
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->discounts = new Discounts();
    }

    /**
     * @return array
     */
    public function dataProviderDiscounts(): array
    {
        return [[[
            'discount' => [
                'label' => ['Discount'],
                'amount' => [
                    'value' => 100,
                    'currency' => 'USD'
                ]
            ]
        ]]];
    }

    /**
     * @dataProvider dataProviderDiscounts
     */
    public function testResolve(array $discount): void
    {
        $expected = [
            [
                'label' => __('Discount'),
                'amount' => [
                    'value' => 100,
                    'currency' => 'USD'
                ]
            ]
        ];

        $result = $this->discounts->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            $discount
        );

        $this->assertEquals($expected, $result);
    }
}
