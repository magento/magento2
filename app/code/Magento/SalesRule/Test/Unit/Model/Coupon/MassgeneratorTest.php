<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Coupon;

/**
 * Tests for Massgenerator
 */
class MassgeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $charset;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $salesRuleCouponMock = $this->createPartialMock(
            \Magento\SalesRule\Helper\Coupon::class,
            ['getCharset', 'getCodeSeparator']
        );

        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Coupon\Massgenerator::class,
            ['salesRuleCoupon' => $salesRuleCouponMock, 'data' => $data]
        );

        $salesRuleCouponMock->expects($this->once())
            ->method('getCharset')
            ->with($data['format'])
            ->willReturn($this->charset);
        $salesRuleCouponMock->method('getCodeSeparator')->willReturn('test-separator');
        $code = $massgenerator->generateCode();

        $this->assertTrue(strlen($code) === $length);
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
        $salesRuleCouponMock = $this->createPartialMock(\Magento\SalesRule\Helper\Coupon::class, ['getCodeSeparator']);
        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Coupon\Massgenerator::class,
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

        $salesRuleCouponMock = $this->createPartialMock(\Magento\SalesRule\Helper\Coupon::class, ['getCharset']);
        $resourceMock = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon::class,
            ['exists', '__wakeup', 'getIdFieldName']
        );
        $dateMock = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, ['gmtTimestamp']);
        $dateTimeMock = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime::class, ['formatDate']);
        $couponFactoryMock = $this->createPartialMock(\Magento\SalesRule\Model\CouponFactory::class, ['create']);
        $couponMock = $this->createPartialMock(
            \Magento\SalesRule\Model\Coupon::class,
            [
                '__wakeup',
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

        $couponMock->expects($this->any())->method('setId')->willReturnSelf();
        $couponMock->expects($this->any())->method('setRuleId')->willReturnSelf();
        $couponMock->expects($this->any())->method('setUsageLimit')->willReturnSelf();
        $couponMock->expects($this->any())->method('setUsagePerCustomer')->willReturnSelf();
        $couponMock->expects($this->any())->method('setCreatedAt')->willReturnSelf();
        $couponMock->expects($this->any())->method('setType')->willReturnSelf();
        $couponMock->expects($this->any())->method('setCode')->willReturnSelf();
        $couponMock->expects($this->any())->method('save')->willReturnSelf();
        $couponFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($couponMock);
        $salesRuleCouponMock->expects($this->any())
            ->method('getCharset')
            ->with($data['format'])
            ->willReturn($this->charset);
        $salesRuleCouponMock->method('getCodeSeparator')->willReturn('test-separator');
        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Coupon\Massgenerator::class,
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
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(
            'We cannot create the requested Coupon Qty. Please check your settings and try again.'
        );

        $data = [
            'qty' => 3,
            'length' => 15,
            'format' => 'test-format',
            'max_attempts' => 0,
        ];

        $salesRuleCouponMock = $this->createPartialMock(
            \Magento\SalesRule\Helper\Coupon::class,
            ['getCharset', 'getCodeSeparator']
        );
        $resourceMock = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon::class,
            ['exists', '__wakeup', 'getIdFieldName']
        );
        $dateMock = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, ['gmtTimestamp']);
        $dateTimeMock = $this->createPartialMock(\Magento\Framework\Stdlib\DateTime::class, ['formatDate']);
        $couponFactoryMock = $this->createPartialMock(\Magento\SalesRule\Model\CouponFactory::class, ['create']);
        $couponMock = $this->createPartialMock(\Magento\SalesRule\Model\Coupon::class, ['__wakeup']);

        $couponFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($couponMock);
        $salesRuleCouponMock->expects($this->any())
            ->method('getCharset')
            ->with($data['format'])
            ->willReturn($this->charset);
        $salesRuleCouponMock->method('getCodeSeparator')->willReturn('test-separator');
        $resourceMock->expects($this->any())
            ->method('exists')
            ->willReturn(true);

        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Coupon\Massgenerator::class,
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
        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(\Magento\SalesRule\Model\Coupon\Massgenerator::class);

        $this->assertEquals($result, $massgenerator->validateData($data));
    }

    /**
     * Run test getGeneratedCount method
     */
    public function testGetGeneratedCount()
    {
        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(\Magento\SalesRule\Model\Coupon\Massgenerator::class);

        $this->assertEquals(0, $massgenerator->getGeneratedCount());
    }

    /**
     * Data for validate test
     *
     * @return array
     */
    public function validateDataProvider()
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
    public function delimiterDataProvider()
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
    public function generatorDataProvider()
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
