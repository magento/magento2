<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\CustomerData;

use PHPUnit\Framework\TestCase;
use Magento\Tax\CustomerData\CheckoutTotalsJsLayoutDataProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Test class to cover CheckoutTotalsJsLayoutDataProvider
 *
 * Class \Magento\Tax\Test\Unit\CustomerData\CheckoutTotalsJsLayoutDataProviderTest
 */
class CheckoutTotalsJsLayoutDataProviderTest extends TestCase
{
    /**
     * @var CheckoutTotalsJsLayoutDataProvider
     */
    private $dataProvider;

    /**
     * @var TaxConfig|PHPUnit\Framework\MockObject\MockObject
     */
    private $taxConfigMock;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $objectManager = new ObjectManagerHelper($this);

        $this->dataProvider = $objectManager->getObject(
            CheckoutTotalsJsLayoutDataProvider::class,
            [
                'taxConfig' => $this->taxConfigMock
            ]
        );
    }

    /**
     * Test getData() with dataset getDataDataProvider
     *
     * @param int $displayCartSubtotalInclTax
     * @param int $displayCartSubtotalExclTax
     * @param array $expected
     * @return void
     * @dataProvider getDataDataProvider
     */
    public function testGetData($displayCartSubtotalInclTax, $displayCartSubtotalExclTax, $expected)
    {
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalInclTax')
            ->willReturn($displayCartSubtotalInclTax);
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalExclTax')
            ->willReturn($displayCartSubtotalExclTax);

        $this->assertEquals($expected, $this->dataProvider->getData());
    }

    /**
     * Dataset for test getData()
     *
     * @return array
     */
    public function getDataDataProvider()
    {
        return [
            'Test with settings display cart incl and excl is Yes' => [
                '1' ,
                '1',
                [
                    'components' => [
                        'minicart_content' => [
                            'children' => [
                                'subtotal.container' => [
                                    'children' => [
                                        'subtotal' => [
                                            'children' => [
                                                'subtotal.totals' => [
                                                    'config' => [
                                                        'display_cart_subtotal_incl_tax' => 1,
                                                        'display_cart_subtotal_excl_tax' => 1
                                                    ]
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ],
            'Test with settings display cart incl and excl is No' => [
                '0' ,
                '0',
                [
                    'components' => [
                        'minicart_content' => [
                            'children' => [
                                'subtotal.container' => [
                                    'children' => [
                                        'subtotal' => [
                                            'children' => [
                                                'subtotal.totals' => [
                                                    'config' => [
                                                        'display_cart_subtotal_incl_tax' => 0,
                                                        'display_cart_subtotal_excl_tax' => 0
                                                    ]
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];
    }
}
