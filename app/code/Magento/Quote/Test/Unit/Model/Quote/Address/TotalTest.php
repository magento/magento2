<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Address;

class TotalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\Total
     */
    protected $model;

    protected function setUp()
    {
        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(function ($value) {
                return json_decode($value, true);
            });

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Quote\Model\Quote\Address\Total::class,
            [
                'serializer' => $serializer
            ]);
    }

    /**
     * @param string $code
     * @param float $amount
     * @param string $storedCode
     * @dataProvider setTotalAmountDataProvider
     */
    public function testSetTotalAmount($code, $amount, $storedCode)
    {
        $result = $this->model->setTotalAmount($code, $amount);
        $this->assertArrayHasKey($storedCode, $result);
        $this->assertEquals($result[$storedCode], $amount);
        $this->assertEquals($this->model->getAllTotalAmounts()[$code], $amount);
        $this->assertSame($this->model, $result);
    }

    public function setTotalAmountDataProvider()
    {
        return [
            'Subtotal' => [
                'code' => 'subtotal',
                'amount' => 42.42,
                'stored_code' => 'subtotal'
            ],
            'Other total' => [
                'code' => 'other',
                'amount' => 42.17,
                'stored_code' => 'other_amount'
            ]
        ];
    }

    /**
     * @param string $code
     * @param float $amount
     * @param string $storedCode
     * @dataProvider setBaseTotalAmountDataProvider
     */
    public function testSetBaseTotalAmount($code, $amount, $storedCode)
    {
        $result = $this->model->setBaseTotalAmount($code, $amount);
        $this->assertArrayHasKey($storedCode, $result);
        $this->assertEquals($result[$storedCode], $amount);
        $this->assertEquals($this->model->getAllBaseTotalAmounts()[$code], $amount);
        $this->assertSame($this->model, $result);
    }

    public function setBaseTotalAmountDataProvider()
    {
        return [
            'Subtotal' => [
                'code' => 'subtotal',
                'amount' => 17.42,
                'stored_code' => 'base_subtotal'
            ],
            'Other total' => [
                'code' => 'other',
                'amount' => 42.17,
                'stored_code' => 'base_other_amount'
            ]
        ];
    }

    /**
     * @param float $initialAmount
     * @param float $delta
     * @param float $updatedAmount
     * @dataProvider addTotalAmountDataProvider
     */
    public function testAddTotalAmount($initialAmount, $delta, $updatedAmount)
    {
        $code = 'turbo';
        $this->model->setTotalAmount($code, $initialAmount);

        $this->assertSame($this->model, $this->model->addTotalAmount($code, $delta));
        $this->assertEquals($updatedAmount, $this->model->getTotalAmount($code));
    }

    public function addTotalAmountDataProvider()
    {
        return [
            'Zero' => [
                'initialAmount' => 0,
                'delta' => 42,
                'updatedAmount' => 42
            ],
            'Non-zero' => [
                'initialAmount' => 20,
                'delta' => 22,
                'updatedAmount' => 42
            ]
        ];
    }

    /**
     * @param float $initialAmount
     * @param float $delta
     * @param float $updatedAmount
     * @dataProvider addBaseTotalAmountDataProvider
     */
    public function testAddBaseTotalAmount($initialAmount, $delta, $updatedAmount)
    {
        $code = 'turbo';
        $this->model->setBaseTotalAmount($code, $initialAmount);

        $this->assertSame($this->model, $this->model->addBaseTotalAmount($code, $delta));
        $this->assertEquals($updatedAmount, $this->model->getBaseTotalAmount($code));
    }

    public function addBaseTotalAmountDataProvider()
    {
        return [
            'Zero' => [
                'initialAmount' => 0,
                'delta' => 42,
                'updatedAmount' => 42
            ],
            'Non-zero' => [
                'initialAmount' => 20,
                'delta' => 22,
                'updatedAmount' => 42
            ]
        ];
    }

    public function testGetTotalAmount()
    {
        $code = 'super';
        $amount = 42;
        $this->model->setTotalAmount($code, $amount);
        $this->assertEquals($amount, $this->model->getTotalAmount($code));
    }

    public function testGetTotalAmountAbsent()
    {
        $this->assertEquals(0, $this->model->getTotalAmount('mega'));
    }

    public function testGetBaseTotalAmount()
    {
        $code = 'wow';
        $amount = 42;
        $this->model->setBaseTotalAmount($code, $amount);
        $this->assertEquals($amount, $this->model->getBaseTotalAmount($code));
    }

    public function testGetBaseTotalAmountAbsent()
    {
        $this->assertEquals(0, $this->model->getBaseTotalAmount('great'));
    }

    /**
     * Verify handling of serialized, non-serialized input into and out of getFullInfo()
     *
     * @covers \Magento\Quote\Model\Quote\Address\Total::getFullInfo()
     * @param $input
     * @param $expected
     * @dataProvider getFullInfoDataProvider
     */
    public function testGetFullInfo($input, $expected)
    {
        $this->model->setFullInfo($input);
        $this->assertEquals($expected, $this->model->getFullInfo());
    }

    /**
     * @return array
     */
    public function getFullInfoDataProvider()
    {
        $myArray = ['team' => 'kiwis'];
        $serializedInput = json_encode($myArray);

        return [
            'simple array' => [
                $myArray,
                $myArray,
            ],

            'serialized array' => [
                $serializedInput,
                $myArray,
            ],

            'null input/output' => [
                null,
                null,
            ],

            'float input' => [
                1.23,
                1.23,
            ],
        ];
    }
}
