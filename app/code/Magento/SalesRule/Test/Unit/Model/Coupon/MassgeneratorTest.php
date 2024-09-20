<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Coupon;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Helper\Coupon;
use Magento\SalesRule\Model\Coupon\Massgenerator;
use Magento\SalesRule\Model\CouponFactory;
use PHPUnit\Framework\TestCase;

class MassgeneratorTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $charset;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->charset = str_split(sha1((string)time()));
    }

    /**
     * Run test generateCode method
     *
     * @param array $data
     * @param int $length
     *
     * @dataProvider generatorDataProvider
     */
    public function testGenerateCode(array $data, $length)
    {
        $salesRuleCouponMock = $this->createPartialMock(Coupon::class, ['getCharset', 'getCodeSeparator']);

        /** @var Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            Massgenerator::class,
            ['salesRuleCoupon' => $salesRuleCouponMock, 'data' => $data]
        );

        $salesRuleCouponMock->expects($this->once())
            ->method('getCharset')
            ->with($data['format'])
            ->willReturn($this->charset);
        $salesRuleCouponMock->method('getCodeSeparator')->willReturn('test-separator');
        $code = $massgenerator->generateCode();

        $this->assertSame($length, strlen($code));
        $this->assertNotEmpty($code);
        if (isset($data['data'])) {
            $this->assertCount($data['length'] / $data['dash'], explode($data['delimiter'], $code));
        }
    }

    /**
     * Run test getDelimiter method
     *
     * @param array $data
     *
     * @dataProvider delimiterDataProvider
     */
    public function testGetDelimiter(array $data)
    {
        $salesRuleCouponMock = $this->createPartialMock(Coupon::class, ['getCodeSeparator']);
        /** @var Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            Massgenerator::class,
            ['salesRuleCoupon' => $salesRuleCouponMock, 'data' => $data]
        );

        if (empty($data['delimiter'])) {
            $salesRuleCouponMock->expects($this->once())
                ->method('getCodeSeparator')
                ->willReturn('test-separator');
            $this->assertEquals('test-separator', $massgenerator->getDelimiter());
        } else {
            $this->assertEquals($data['delimiter'], $massgenerator->getDelimiter());
        }
    }

    /**
     * Run test generatePool method
     */
    public function testGeneratePool()
    {
        $qty = 10;
        $data = [
            'qty' => $qty,
            'length' => 15,
            'format' => 'test-format',
        ];

        $salesRuleCouponMock = $this->createPartialMock(Coupon::class, ['getCharset', 'getCodeSeparator']);
        $resourceMock = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon::class,
            ['exists', 'getIdFieldName']
        );
        $dateMock = $this->createPartialMock(DateTime::class, ['gmtTimestamp']);
        $dateTimeMock = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime::class, ['formatDate']);
        $couponFactoryMock = $this->createPartialMock(CouponFactory::class, ['create']);
        $couponMock = $this->createPartialMock(
            \Magento\SalesRule\Model\Coupon::class,
            [
                'setId',
                'setRuleId',
                'setUsageLimit',
                'setUsagePerCustomer',
                'setCreatedAt',
                'setType',
                'setCode',
                'save'
            ]
        );

        $couponMock->method('setId')->willReturnSelf();
        $couponMock->method('setRuleId')->willReturnSelf();
        $couponMock->method('setUsageLimit')->willReturnSelf();
        $couponMock->method('setUsagePerCustomer')->willReturnSelf();
        $couponMock->method('setCreatedAt')->willReturnSelf();
        $couponMock->method('setType')->willReturnSelf();
        $couponMock->method('setCode')->willReturnSelf();
        $couponMock->method('save')->willReturnSelf();
        $couponFactoryMock->expects($this->once())->method('create')->willReturn($couponMock);
        $salesRuleCouponMock->method('getCharset')->with($data['format'])->willReturn($this->charset);
        $salesRuleCouponMock->method('getCodeSeparator')->willReturn('test-separator');
        /** @var Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            Massgenerator::class,
            [
                'couponFactory' => $couponFactoryMock,
                'dateTime' => $dateTimeMock,
                'date' => $dateMock,
                'resource' => $resourceMock,
                'data' => $data,
                'salesRuleCoupon' => $salesRuleCouponMock
            ]
        );

        $this->assertEquals($massgenerator, $massgenerator->generatePool());
        $this->assertEquals($qty, $massgenerator->getGeneratedCount());
        $codes = $massgenerator->getGeneratedCodes();
        ($qty > 0) ? $this->assertNotEmpty($codes) : $this->assertEmpty($codes);
    }

    /**
     * Run test generatePool method (throw exception)
     */
    public function testGeneratePoolException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'We cannot create the requested Coupon Qty. Please check your settings and try again.'
        );
        $data = [
            'qty' => 3,
            'length' => 15,
            'format' => 'test-format',
            'max_attempts' => 0,
        ];

        $salesRuleCouponMock = $this->createPartialMock(Coupon::class, ['getCharset', 'getCodeSeparator']);
        $resourceMock = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon::class,
            ['exists', 'getIdFieldName']
        );
        $dateMock = $this->createPartialMock(DateTime::class, ['gmtTimestamp']);
        $dateTimeMock = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime::class, ['formatDate']);
        $couponFactoryMock = $this->createPartialMock(CouponFactory::class, ['create']);
        $couponMock = $this->createMock(\Magento\SalesRule\Model\Coupon::class);

        $couponFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($couponMock);
        $salesRuleCouponMock
            ->method('getCharset')
            ->with($data['format'])
            ->willReturn($this->charset);
        $salesRuleCouponMock->method('getCodeSeparator')->willReturn('test-separator');
        $resourceMock
            ->method('exists')
            ->willReturn(true);

        /** @var Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            Massgenerator::class,
            [
                'couponFactory' => $couponFactoryMock,
                'dateTime' => $dateTimeMock,
                'date' => $dateMock,
                'resource' => $resourceMock,
                'data' => $data,
                'salesRuleCoupon' => $salesRuleCouponMock
            ]
        );

        $this->assertEquals($massgenerator, $massgenerator->generatePool());
    }

    /**
     * Run test validateData method
     *
     * @param array $data
     * @param bool $result
     *
     * @dataProvider validateDataProvider
     */
    public function testValidateData(array $data, $result)
    {
        /** @var Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(Massgenerator::class);

        $this->assertEquals($result, $massgenerator->validateData($data));
    }

    /**
     * Run test getGeneratedCount method
     */
    public function testGetGeneratedCount()
    {
        /** @var Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(Massgenerator::class);

        $this->assertEquals(0, $massgenerator->getGeneratedCount());
    }

    /**
     * Data for validate test
     *
     * @return array
     */
    public static function validateDataProvider()
    {
        return [
            [
                'data' => [
                    'qty' => 20,
                    'rule_id' => 1,
                    'length' => 15,
                    'format' => 'test-format',
                ],
                'result' => true,
            ],
            [
                'data' => [
                    'qty' => 0,
                    'rule_id' => 1,
                    'length' => 15,
                    'format' => 'test-format',
                ],
                'result' => false
            ],
            [
                'data' => [
                    'qty' => 0,
                    'rule_id' => 1,
                    'length' => 15,
                    'format' => '',
                ],
                'result' => false
            ],
            [
                'data' => [
                    'qty' => 2,
                    'length' => 15,
                ],
                'result' => false
            ]
        ];
    }

    /**
     * Data for test getDelimiter method
     *
     * @return array
     */
    public static function delimiterDataProvider()
    {
        return [
            [
                'data' => [
                    'delimiter' => 'delimiter-value',
                ],
            ],
            [
                'data' => []
            ]
        ];
    }

    /**
     * Data for code generation coupon
     *
     * @return array
     */
    public static function generatorDataProvider()
    {
        return [
            [
                'data' => [
                    'format' => 'test-format',
                    'length' => 10,
                ],
                'length' => 10,
            ],
            [
                'data' => [
                    'format' => 'test-format',
                    'length' => 18,
                    'dash' => 6,
                    'delimiter' => '-',
                ],
                'length' => 20
            ]
        ];
    }
}
