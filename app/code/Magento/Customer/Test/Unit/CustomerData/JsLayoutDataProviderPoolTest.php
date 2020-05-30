<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\CustomerData;

use Magento\Customer\CustomerData\JsLayoutDataProviderPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Tax\CustomerData\CheckoutTotalsJsLayoutDataProvider as CheckoutTotalsJs;
use PHPUnit\Framework\TestCase;

class JsLayoutDataProviderPoolTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CheckoutTotalsJs
     */
    private $checkoutTotalsJsLayoutDataProviderMock;

    /**
     * @var JsLayoutDataProviderPool
     */
    private $jsLayoutDataProviderPool;

    /**
     * Setup environment to test
     */
    protected function setUp(): void
    {
        $this->checkoutTotalsJsLayoutDataProviderMock = $this->createMock(CheckoutTotalsJs::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->jsLayoutDataProviderPool = $this->objectManagerHelper->getObject(
            JsLayoutDataProviderPool::class,
            [
                'jsLayoutDataProviders' => [
                    'checkout_totals' => $this->checkoutTotalsJsLayoutDataProviderMock
                ]
            ]
        );
    }

    /**
     * Test getData() function
     */
    public function testGetData()
    {
        $checkoutTotalsJsData = [
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
        ];
        $this->checkoutTotalsJsLayoutDataProviderMock->expects($this->any())
            ->method('getData')
            ->willReturn($checkoutTotalsJsData);

        $this->assertEquals($checkoutTotalsJsData, $this->jsLayoutDataProviderPool->getData());
    }
}
