<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items;

/**
 * Data provider class for ShipmentTest class
 */
class ShipmentTestProvider
{
    /**
     * Returns shipment test data
     *
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getData(): array
    {
        return
            [
                [
                    [
                        [
                            'lines' =>
                                [
                                    [
                                        [
                                            'text' => 0,
                                            'feed' => 35,
                                        ],
                                        [
                                            'text' =>
                                                [
                                                    'Simple2',
                                                ],
                                            'feed' => 100,
                                        ],
                                        [
                                            'text' =>
                                                [
                                                    'simple2',
                                                ],
                                            'feed' => 565,
                                            'align' => 'right',
                                        ],
                                    ],
                                ],
                            'height' => 15,
                        ],
                        [
                            'lines' =>
                                [
                                    [
                                        [
                                            'text' => 0,
                                            'feed' => 35,
                                        ],
                                        [
                                            'text' =>
                                                [
                                                    'Bundle',
                                                ],
                                            'feed' => 100,
                                        ],
                                        [
                                            'text' =>
                                                [
                                                    'bundle-simple',
                                                ],
                                            'feed' => 565,
                                            'align' => 'right',
                                        ],
                                    ],
                                    [
                                        [
                                            'text' => 0,
                                            'feed' => 35,
                                        ],
                                        [
                                            'text' =>
                                                [
                                                    'Simple1',
                                                ],
                                            'feed' => 100,
                                        ],
                                        [
                                            'text' =>
                                                [
                                                    'simple1',
                                                ],
                                            'feed' => 565,
                                            'align' => 'right',
                                        ],
                                    ],
                                ],
                            'height' => 15,
                        ],
                    ]
                ]
            ];
    }
}
