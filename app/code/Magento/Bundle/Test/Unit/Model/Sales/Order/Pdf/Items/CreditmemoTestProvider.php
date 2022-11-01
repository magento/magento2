<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items;

/**
 * Data provider class for CresitmemoTest class
 */
class CreditmemoTestProvider
{
    /**
     * Returns creditmemo test data
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
                        1 =>
                            [
                                'lines' =>
                                    [
                                        [
                                            [
                                                'font' => 'italic',
                                                'text' =>
                                                    [
                                                        'test option',
                                                    ],
                                                'feed' => 35,
                                            ],
                                        ],
                                        [
                                            [
                                                'text' =>
                                                    [
                                                        'Simple1',
                                                    ],
                                                'feed' => 40,
                                            ],
                                            [
                                                'text' => '20.00',
                                                'feed' => 285,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 50,
                                            ],
                                            [
                                                'text' => 0,
                                                'feed' => 335,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 50,
                                            ],
                                            [
                                                'text' => 2,
                                                'feed' => 420,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 30,
                                            ],
                                            [
                                                'text' => '1.66',
                                                'feed' => 455,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 45,
                                            ],
                                            [
                                                'text' => 21.66,
                                                'feed' => 565,
                                                'font' => 'bold',
                                                'align' => 'right',
                                            ],
                                        ],
                                        [
                                            [
                                                'text' =>
                                                    [
                                                        'Simple2',
                                                    ],
                                                'feed' => 40,
                                            ],
                                            [
                                                'text' => '10.00',
                                                'feed' => 285,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 50,
                                            ],
                                            [
                                                'text' => 0,
                                                'feed' => 335,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 50,
                                            ],
                                            [
                                                'text' => 2,
                                                'feed' => 420,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 30,
                                            ],
                                            [
                                                'text' => '0.83',
                                                'feed' => 455,
                                                'font' => 'bold',
                                                'align' => 'right',
                                                'width' => 45,
                                            ],
                                            [
                                                'text' => 10.83,
                                                'feed' => 565,
                                                'font' => 'bold',
                                                'align' => 'right',
                                            ],
                                        ],
                                    ],
                                'height' => 15,
                            ],
                    ],
                ],
            ];
    }
}
