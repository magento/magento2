<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items;

/**
 * Data provider class for InvoiceTest class
 */
class InvoiceTestProvider
{
    /**
     * Returns invoice test variations data
     *
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getData(): array
    {
        return [
            'display_both' => [
                'expected' => [
                    1 => [
                        'height' => 15,
                        'lines' => [
                            [
                                [
                                    'text' => 'test option',
                                    'feed' => 35,
                                    'font' => 'italic',

                                ],
                            ],
                            [
                                [
                                    'text' => 'Simple1',
                                    'feed' => 40,
                                ],
                                [
                                    'text' => 2,
                                    'feed' => 435,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 1.66,
                                    'feed' => 495,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 'Excl. Tax:',
                                    'feed' => 380,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 'Excl. Tax:',
                                    'feed' => 565,
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => '10.00',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '20.00',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => 'Incl. Tax:',
                                    'feed' => 380,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 'Incl. Tax:',
                                    'feed' => 565,
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => '10.83',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '21.66',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => 'Simple2',
                                    'feed' => 40,
                                ],
                                [
                                    'text' => 2,
                                    'feed' => 435,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 0.83,
                                    'feed' => 495,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 'Excl. Tax:',
                                    'feed' => 380,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 'Excl. Tax:',
                                    'feed' => 565,
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => '5.00',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '10.00',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => 'Incl. Tax:',
                                    'feed' => 380,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 'Incl. Tax:',
                                    'feed' => 565,
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => '5.41',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '10.83',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                        ],
                    ],
                ],
                'tax_mock_method' => 'displaySalesBothPrices',
            ],
            'including_tax' => [
                'expected' => [
                    1 => [
                        'height' => 15,
                        'lines' => [
                            [
                                [
                                    'text' => 'test option',
                                    'feed' => 35,
                                    'font' => 'italic',
                                ],
                            ],
                            [
                                [
                                    'text' => 'Simple1',
                                    'feed' => 40,
                                ],
                                [
                                    'text' => 2,
                                    'feed' => 435,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 1.66,
                                    'feed' => 495,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '10.83',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '21.66',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => 'Simple2',
                                    'feed' => 40,
                                ],
                                [
                                    'text' => 2,
                                    'feed' => 435,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 0.83,
                                    'feed' => 495,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '5.41',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '10.83',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                        ],
                    ],
                ],
                'tax_mock_method' => 'displaySalesPriceInclTax',
            ],
            'excluding_tax' => [
                'expected' => [
                    1 => [
                        'height' => 15,
                        'lines' => [
                            [
                                [
                                    'text' => 'test option',
                                    'feed' => 35,
                                    'font' => 'italic',

                                ],
                            ],
                            [
                                [
                                    'text' => 'Simple1',
                                    'feed' => 40,
                                ],
                                [
                                    'text' => 2,
                                    'feed' => 435,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 1.66,
                                    'feed' => 495,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '10.00',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '20.00',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                            [
                                [
                                    'text' => 'Simple2',
                                    'feed' => 40,
                                ],
                                [
                                    'text' => 2,
                                    'feed' => 435,
                                    'align' => 'right',
                                ],
                                [
                                    'text' => 0.83,
                                    'feed' => 495,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '5.00',
                                    'feed' => 380,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                                [
                                    'text' => '10.00',
                                    'feed' => 565,
                                    'font' => 'bold',
                                    'align' => 'right',
                                ],
                            ],
                        ],
                    ],
                ],
                'tax_mock_method' => 'displaySalesPriceExclTax',
            ],
        ];
    }
}
