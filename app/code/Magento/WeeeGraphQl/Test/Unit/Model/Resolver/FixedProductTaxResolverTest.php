<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\WeeeGraphQl\Model\Resolver\Quote\FixedProductTax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test FPT resolver for cart item
 */
class FixedProductTaxResolverTest extends TestCase
{
    /**
     * @var MockObject|ContextInterface
     */
    private $contextMock;

    /**
     * @var MockObject|WeeeHelper
     */
    private $weeeHelperMock;

    /**
     * @var TaxHelper|MockObject
     */
    private $taxHelperMock;

    /**
     * @var FixedProductTax
     */
    private $resolver;

    /**
     * @var array[]
     */
    private $fpts = [
        [
            "title" =>  "FPT 2",
            "base_amount" =>  "0.5000",
            "amount" =>  0.5,
            "row_amount" =>  1.0,
            "base_row_amount" =>  1.0,
            "base_amount_incl_tax" =>  "0.5500",
            "amount_incl_tax" =>  0.55,
            "row_amount_incl_tax" =>  1.1,
            "base_row_amount_incl_tax" =>  1.1
        ],
        [
            "title" =>  "FPT 1",
            "base_amount" =>  "1.0000",
            "amount" =>  1,
            "row_amount" =>  2,
            "base_row_amount" =>  2,
            "base_amount_incl_tax" =>  "1.1000",
            "amount_incl_tax" =>  1.1,
            "row_amount_incl_tax" =>  2.2,
            "base_row_amount_incl_tax" =>  2.2
        ],
        [
            "title" =>  "FPT 2",
            "base_amount" =>  "1.5000",
            "amount" =>  1.5,
            "row_amount" =>  3.0,
            "base_row_amount" =>  3.0,
            "base_amount_incl_tax" =>  "1.6500",
            "amount_incl_tax" =>  1.65,
            "row_amount_incl_tax" =>  3.30,
            "base_row_amount_incl_tax" =>  3.30
        ]
    ];

    /**
     * @var ContextExtensionInterface|MockObject
     */
    private $contextExtensionAttributesMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var CartItemInterface|MockObject
     */
    private $cartItemMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->addMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();

        $this->weeeHelperMock = $this->getMockBuilder(WeeeHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEnabled', 'getApplied'])
            ->getMock();
        $this->taxHelperMock = $this->getMockBuilder(TaxHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPriceDisplayType'])
            ->getMock();

        $this->contextExtensionAttributesMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->addMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->cartItemMock = $this->createMock(CartItemInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->resolver = new FixedProductTax($this->weeeHelperMock, $this->taxHelperMock);
    }

    /**
     * Verifies that exception is thrown if model is not specified.
     *
     * @return void
     */
    public function testShouldThrowException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/value should be specified/');

        $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock);
    }

    /**
     * Verifies that result is empty if FPT config is disabled.
     *
     * @return void
     */
    public function testShouldReturnEmptyResult(): void
    {
        $this->contextExtensionAttributesMock->method('getStore')
            ->willreturn($this->storeMock);
        $this->contextMock->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionAttributesMock);

        $this->weeeHelperMock->method('isEnabled')
            ->with($this->storeMock)
            ->willReturn(false);

        $this->weeeHelperMock->expects($this->never())
            ->method('getApplied');

        $this->assertEquals(
            [],
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                ['model' => $this->cartItemMock]
            )
        );
    }

    /**
     * @param int $displayType
     * @param array $expected
     *
     * @return void
     * @dataProvider shouldReturnResultDataProvider
     */
    public function testShouldReturnResult(int $displayType, array $expected): void
    {
        $this->contextExtensionAttributesMock->method('getStore')
            ->willreturn($this->storeMock);
        $this->contextMock->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionAttributesMock);

        $this->weeeHelperMock->method('isEnabled')
            ->with($this->storeMock)
            ->willReturn(true);

        $this->weeeHelperMock->expects($this->once())
            ->method('getApplied')
            ->willReturn($this->fpts);

        $this->taxHelperMock->expects($this->once())
            ->method('getPriceDisplayType')
            ->willReturn($displayType);

        $this->assertEquals(
            $expected,
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                [
                    'model' => $this->cartItemMock,
                    'price' => [
                        'currency' => 'USD'
                    ]
                ]
            )
        );
    }

    /**
     * @return array
     */
    public function shouldReturnResultDataProvider(): array
    {
        return [
            [
                1,
                [
                    [
                        'label' => 'FPT 2',
                        'amount' => [
                            'value' => 0.5,
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        'label' => 'FPT 1',
                        'amount' => [
                            'value' => 1,
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        'label' => 'FPT 2',
                        'amount' => [
                            'value' => 1.5,
                            'currency' => 'USD'
                        ]
                    ]
                ]
            ],
            [
                2,
                [
                    [
                        'label' => 'FPT 2',
                        'amount' => [
                            'value' => 0.55,
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        'label' => 'FPT 1',
                        'amount' => [
                            'value' => 1.1,
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        'label' => 'FPT 2',
                        'amount' => [
                            'value' => 1.65,
                            'currency' => 'USD'
                        ]
                    ]
                ]
            ]
        ];
    }
}
