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
    private $context;

    /**
     * @var MockObject|WeeeHelper
     */
    private $weeeHelper;

    /**
     * @var TaxHelper|MockObject
     */
    private $taxHelper;

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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();

        $this->weeeHelper = $this->getMockBuilder(WeeeHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEnabled', 'getApplied'])
            ->getMock();
        $this->taxHelper = $this->getMockBuilder(TaxHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPriceDisplayType'])
            ->getMock();

        $this->resolver = new FixedProductTax(
            $this->weeeHelper,
            $this->taxHelper,
        );
    }

    /**
     * Verifies that exception is thrown if model is not specified
     */
    public function testShouldThrowException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/value should be specified/');

        $this->resolver->resolve(
            $this->getFieldStub(),
            null,
            $this->getResolveInfoStub()
        );
    }

    /**
     * Verifies that result is empty if FPT config is disabled
     */
    public function testShouldReturnEmptyResult(): void
    {
        $store = $this->createMock(StoreInterface::class);
        $cartItem = $this->createMock(CartItemInterface::class);
        $contextExtensionAttributes = $this->createMock(ContextExtensionInterface::class);
        $contextExtensionAttributes->method('getStore')
            ->willreturn($store);
        $this->context->method('getExtensionAttributes')
            ->willReturn($contextExtensionAttributes);

        $this->weeeHelper->method('isEnabled')
            ->with($store)
            ->willReturn(false);

        $this->weeeHelper->expects($this->never())
            ->method('getApplied');

        $this->assertEquals(
            [],
            $this->resolver->resolve(
                $this->getFieldStub(),
                $this->context,
                $this->getResolveInfoStub(),
                ['model' => $cartItem]
            )
        );
    }

    /**
     * @dataProvider shouldReturnResultDataProvider
     * @param int $displayType
     * @param array $expected
     */
    public function testShouldReturnResult(int $displayType, array $expected): void
    {
        $store = $this->createMock(StoreInterface::class);
        $cartItem = $this->createMock(CartItemInterface::class);
        $contextExtensionAttributes = $this->createMock(ContextExtensionInterface::class);
        $contextExtensionAttributes->method('getStore')
            ->willreturn($store);
        $this->context->method('getExtensionAttributes')
            ->willReturn($contextExtensionAttributes);

        $this->weeeHelper->method('isEnabled')
            ->with($store)
            ->willReturn(true);

        $this->weeeHelper->expects($this->once())
            ->method('getApplied')
            ->willReturn($this->fpts);

        $this->taxHelper->expects($this->once())
            ->method('getPriceDisplayType')
            ->willReturn($displayType);

        $this->assertEquals(
            $expected,
            $this->resolver->resolve(
                $this->getFieldStub(),
                $this->context,
                $this->getResolveInfoStub(),
                [
                    'model' => $cartItem,
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

    /**
     * @return MockObject|Field
     */
    private function getFieldStub(): Field
    {
        /** @var MockObject|Field $fieldMock */
        $fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $fieldMock;
    }

    /**
     * @return MockObject|ResolveInfo
     */
    private function getResolveInfoStub(): ResolveInfo
    {
        /** @var MockObject|ResolveInfo $resolveInfoMock */
        $resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $resolveInfoMock;
    }
}
