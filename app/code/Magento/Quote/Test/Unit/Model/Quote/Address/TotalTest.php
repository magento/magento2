<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address\Total;
use PHPUnit\Framework\TestCase;

class TotalTest extends TestCase
{
    /**
     * @var Total
     */
    protected $model;

    protected function setUp(): void
    {
        $serializer = $this->getMockBuilder(Json::class)
            ->onlyMethods(['unserialize'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(function ($value) {
                return json_decode($value, true);
            });

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Total::class,
            [
                'serializer' => $serializer
            ]
        );
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

    /**
     * @return array
     */
    public static function setTotalAmountDataProvider()
    {
        return [
            'Subtotal' => [
                'code' => 'subtotal',
                'amount' => 42.42,
                'storedCode' => 'subtotal'
            ],
            'Other total' => [
                'code' => 'other',
                'amount' => 42.17,
                'storedCode' => 'other_amount'
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

    /**
     * @return array
     */
    public static function setBaseTotalAmountDataProvider()
    {
        return [
            'Subtotal' => [
                'code' => 'subtotal',
                'amount' => 17.42,
                'storedCode' => 'base_subtotal'
            ],
            'Other total' => [
                'code' => 'other',
                'amount' => 42.17,
                'storedCode' => 'base_other_amount'
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

    /**
     * @return array
     */
    public static function addTotalAmountDataProvider()
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

    /**
     * @return array
     */
    public static function addBaseTotalAmountDataProvider()
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
    public static function getFullInfoDataProvider()
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
