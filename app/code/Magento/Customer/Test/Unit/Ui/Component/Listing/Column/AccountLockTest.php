<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\AccountLock;

class AccountLockTest extends \PHPUnit_Framework_TestCase
{
    /** @var AccountLock */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory */
    protected $uiComponentFactory;

    public function setup()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->getMock(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            [],
            [],
            '',
            false
        );
        $this->component = new AccountLock(
            $this->context,
            $this->uiComponentFactory
        );
    }

    /**
     * @param string $lockExpirationDate
     * @param \Magento\Framework\Phrase $expectedResult
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
    public function testPrepareDataSourceDataProvider()
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
                                'lock_expires' => new \Magento\Framework\Phrase('Unlocked')
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
                                'lock_expires' => new \Magento\Framework\Phrase('Unlocked')
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
                                'lock_expires' => new \Magento\Framework\Phrase('Unlocked')
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
                                'lock_expires' => new \Magento\Framework\Phrase('Locked')
                            ],
                        ]
                    ]
                ]
            ],
        ];
    }
}
