<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\Data\PaymentMethodInterfaceFactory;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\PaymentMethodList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PaymentMethodListTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var PaymentMethodList|MockObject
     */
    private $paymentMethodList;

    /**
     * @var PaymentMethodInterfaceFactory|MockObject
     */
    private $methodFactoryMock;

    /**
     * @var \Magento\Payment\Helper\Data|MockObject
     */
    private $helperMock;

    /**
     * Setup.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->methodFactoryMock = $this->createPartialMock(
            PaymentMethodInterfaceFactory::class,
            ['create']
        );
        $this->helperMock = $this->createMock(Data::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->paymentMethodList = $this->objectManagerHelper->getObject(
            PaymentMethodList::class,
            [
                'methodFactory' => $this->methodFactoryMock,
                'helper' => $this->helperMock
            ]
        );
    }

    /**
     * Setup getList method.
     *
     * @param array $paymentMethodConfig
     * @param array $methodInstancesMap
     * @return void
     */
    private function setUpGetList($paymentMethodConfig, $methodInstancesMap)
    {
        $this->helperMock->expects($this->once())
            ->method('getPaymentMethods')
            ->willReturn($paymentMethodConfig);
        $this->helperMock->expects($this->any())
            ->method('getMethodInstance')
            ->willReturnMap($methodInstancesMap);

        $this->methodFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $paymentMethod = $this->createMock(PaymentMethodInterface::class);
                $paymentMethod->expects($this->any())
                    ->method('getCode')
                    ->willReturn($data['code']);
                $paymentMethod->expects($this->any())
                    ->method('getIsActive')
                    ->willReturn($data['isActive']);

                return $paymentMethod;
            });
    }

    /**
     * Test getList.
     *
     * @param int $storeId
     * @param array $paymentMethodConfig
     * @param array $methodInstancesMap
     * @param array $expected
     * @return void
     */
    #[DataProvider('getListDataProvider')]
    public function testGetList($storeId, $paymentMethodConfig, $methodInstancesMap, $expected)
    {
        $methodInstancesMapFinal = [];
        $i = 0;
        foreach ($methodInstancesMap as $methodInstances) {
            $methodInstancesMapFinal[$i][0] = $methodInstances[0];
            $methodInstancesMapFinal[$i][1] = $methodInstances[1]($this);
            $i++;
        }
        $this->setUpGetList($paymentMethodConfig, $methodInstancesMapFinal);

        $codes = array_map(
            function ($method) {
                return $method->getCode();
            },
            $this->paymentMethodList->getList($storeId)
        );

        $this->assertEquals($expected, $codes);
    }

    /**
     * Data provider for getList.
     *
     * @return array
     */
    public static function getListDataProvider()
    {
        return [
            [
                1,
                ['method_code_1' => [], 'method_code_2' => []],
                [
                    [
                        'method_code_1',
                        static fn (self $testCase) => $testCase->mockPaymentMethodInstance(
                            1,
                            10,
                            'method_code_1',
                            'title',
                            true
                        )
                    ],
                    [
                        'method_code_2',
                        static fn (self $testCase) => $testCase->mockPaymentMethodInstance(
                            1,
                            5,
                            'method_code_2',
                            'title',
                            true
                        )
                    ]
                ],
                ['method_code_2', 'method_code_1']
            ]
        ];
    }

    /**
     * Test getActiveList.
     *
     * @param int $storeId
     * @param array $paymentMethodConfig
     * @param array $methodInstancesMap
     * @param array $expected
     * @return void
     */
    #[DataProvider('getActiveListDataProvider')]
    public function testGetActiveList($storeId, $paymentMethodConfig, $methodInstancesMap, $expected)
    {
        $methodInstancesMapFinal = [];
        $i = 0;
        foreach ($methodInstancesMap as $methodInstances) {
            $methodInstancesMapFinal[$i][0] = $methodInstances[0];
            $methodInstancesMapFinal[$i][1] = $methodInstances[1]($this);
            $i++;
        }
        $this->setUpGetList($paymentMethodConfig, $methodInstancesMapFinal);

        $codes = array_map(
            function ($method) {
                return $method->getCode();
            },
            $this->paymentMethodList->getActiveList($storeId)
        );

        $this->assertEquals($expected, $codes);
    }

    /**
     * Data provider for getActiveList.
     *
     * @return array
     */
    public static function getActiveListDataProvider()
    {
        return [
            [
                1,
                ['method_code_1' => [], 'method_code_2' => []],
                [
                    [
                        'method_code_1',
                        static fn (self $testCase) => $testCase->mockPaymentMethodInstance(
                            1,
                            10,
                            'method_code_1',
                            'title',
                            false
                        )
                    ],
                    [
                        'method_code_2',
                        static fn (self $testCase) => $testCase->mockPaymentMethodInstance(
                            1,
                            5,
                            'method_code_2',
                            'title',
                            true
                        )
                    ]
                ],
                ['method_code_2']
            ]
        ];
    }

    /**
     * Mock payment method instance.
     *
     * @param int $storeId
     * @param int $sortOrder
     * @param string $code
     * @param string $title
     * @param bool $isActive
     * @return MockObject
     */
    private function mockPaymentMethodInstance($storeId, $sortOrder, $code, $title, $isActive)
    {
        $paymentMethodInstance = $this->createMock(AbstractMethod::class);
        $paymentMethodInstance->expects($this->any())
            ->method('getConfigData')
            ->willReturnMap([
                ['sort_order', $storeId, $sortOrder]
            ]);
        $paymentMethodInstance->expects($this->any())
            ->method('getCode')
            ->willReturn($code);
        $paymentMethodInstance->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);
        $paymentMethodInstance->expects($this->any())
            ->method('isActive')
            ->willReturn($isActive);

        return $paymentMethodInstance;
    }
}
