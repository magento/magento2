<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\AccountLock;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\TestCase;

class AccountLockTest extends TestCase
{
    /** @var AccountLock */
    protected $component;

    /** @var ContextInterface */
    protected $context;

    /** @var UiComponentFactory */
    protected $uiComponentFactory;

    protected function setup(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->component = new AccountLock(
            $this->context,
            $this->uiComponentFactory
        );
    }

    /**
     * @param string $lockExpirationDate
     * @param Phrase $expectedResult
     * @dataProvider testPrepareDataSourceDataProvider
     */
    public function testPrepareDataSource($lockExpirationDate, $expectedResult)
    {
        $dataSource = $this->component->prepareDataSource($lockExpirationDate);

        $this->assertEquals($expectedResult, $dataSource);
    }

    /**
     * @return array
     */
    public static function testPrepareDataSourceDataProvider()
    {
        return [
            [
                'lockExpirationDate' => [
                    'data' => [
                        'items' => [['lock_expires' => null]],
                    ]
                ],
                'expectedResult' => [
                    'data' => [
                        'items' => [
                            [
                                'lock_expires' => new Phrase('Unlocked')
                            ],
                        ]
                    ]
                ]
            ],
            [
                'lockExpirationDate' => [
                    'data' => [
                        'items' => [[]]//Non exist lock_expires data
                    ]
                ],
                'expectedResult' => [
                    'data' => [
                        'items' => [
                            [
                                'lock_expires' => new Phrase('Unlocked')
                            ],
                        ]
                    ]
                ]
            ],
            [
                'lockExpirationDate' => [
                    'data' => [
                        'items' => [
                            [
                                'lock_expires' => date("F j, Y", strtotime('-1 days'))
                            ],
                        ]
                    ]
                ],
                'expectedResult' => [
                    'data' => [
                        'items' => [
                            [
                                'lock_expires' => new Phrase('Unlocked')
                            ],
                        ]
                    ]
                ]
            ],
            [
                'lockExpirationDate' => [
                    'data' => [
                        'items' => [
                            [
                                'lock_expires' => date("F j, Y", strtotime('+1 days'))
                            ],
                        ]
                    ]
                ],
                'expectedResult' => [
                    'data' => [
                        'items' => [
                            [
                                'lock_expires' => new Phrase('Locked')
                            ],
                        ]
                    ]
                ]
            ],
        ];
    }
}
