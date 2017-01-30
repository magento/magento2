<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
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
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'lock_expires' => $lockExpirationDate
                    ],
                ]
            ]
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'lock_expires' => $expectedResult,
                    ],
                ]
            ]
        ];
        $dataSource = $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedDataSource, $dataSource);
    }

    /**
     * @return array
     */
    public function testPrepareDataSourceDataProvider()
    {
        return [
            [
                'lockExpirationDate' => date("F j, Y", strtotime('-1 days')),
                'expectedResult' => new \Magento\Framework\Phrase('Unlocked')
            ],
            [
                'lockExpirationDate' => date("F j, Y", strtotime('+1 days')),
                'expectedResult' => new \Magento\Framework\Phrase('Locked')
            ]
        ];
    }
}
