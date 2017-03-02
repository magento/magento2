<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Coupon;

/**
 * Class MassgeneratorTest
 */
class MassgeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $charset;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->charset = str_split(md5((string)time()));
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
        $salesRuleCouponMock = $this->getMock(\Magento\SalesRule\Helper\Coupon::class, ['getCharset'], [], '', false);

        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Coupon\Massgenerator::class,
            ['salesRuleCoupon' => $salesRuleCouponMock, 'data' => $data]
        );

        $salesRuleCouponMock->expects($this->once())
            ->method('getCharset')
            ->with($data['format'])
            ->will($this->returnValue($this->charset));
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
        $salesRuleCouponMock = $this->getMock(
            \Magento\SalesRule\Helper\Coupon::class,
            ['getCodeSeparator'],
            [],
            '',
            false
        );
        /** @var \Magento\SalesRule\Model\Coupon\Massgenerator $massgenerator */
        $massgenerator = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Coupon\Massgenerator::class,
            ['salesRuleCoupon' => $salesRuleCouponMock, 'data' => $data]
        );

        if (empty($data['delimiter'])) {
            $salesRuleCouponMock->expects($this->once())
                ->method('getCodeSeparator')
                ->will($this->returnValue('test-separator'));
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

        $salesRuleCouponMock = $this->getMock(\Magento\SalesRule\Helper\Coupon::class, ['getCharset'], [], '', false);
        $resourceMock = $this->getMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon::class,
            ['exists', '__wakeup', 'getIdFieldName'],
            [],
            '',
            false
        );
        $dateMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, ['gmtTimestamp'], [], '', false);
        $dateTimeMock = $this->getMock(\Magento\Framework\Stdlib\DateTime::class, ['formatDate'], [], '', false);
        $couponFactoryMock = $this->getMock(\Magento\SalesRule\Model\CouponFactory::class, ['create'], [], '', false);
        $couponMock = $this->getMock(
            \Magento\SalesRule\Model\Coupon::class,
            [
                '__wakeup',
                'setId',
                'setRuleId',
                'setUsageLimit',
                'setUsagePerCustomer',
                'setExpirationDate',
                'setCreatedAt',
                'setType',
                'setCode',
                'save'
            ],
            [],
            '',
            false
        );

        $couponMock->expects($this->any())->method('setId')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('setRuleId')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('setUsageLimit')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('setUsagePerCustomer')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('setExpirationDate')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('setCreatedAt')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('setType')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('setCode')->will($this->returnSelf());
        $couponMock->expects($this->any())->method('save')->will($this->returnSelf());
        $couponFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($couponMock));
        $salesRuleCouponMock->expects($this->any())
            ->method('getCharset')
            ->with($data['format'])
            ->will($this->returnValue($this->charset));
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We cannot create the requested Coupon Qty. Please check your settings and try again.
     */
    public function testGeneratePoolException()
    {
        $data = [
            'qty' => 3,
            'length' => 15,
            'format' => 'test-format',
            'max_attempts' => 0,
        ];

        $salesRuleCouponMock = $this->getMock(\Magento\SalesRule\Helper\Coupon::class, ['getCharset'], [], '', false);
        $resourceMock = $this->getMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon::class,
            ['exists', '__wakeup', 'getIdFieldName'],
            [],
            '',
            false
        );
        $dateMock = $this->getMock(\Magento\Framework\Stdlib\DateTime\DateTime::class, ['gmtTimestamp'], [], '', false);
        $dateTimeMock = $this->getMock(\Magento\Framework\Stdlib\DateTime::class, ['formatDate'], [], '', false);
        $couponFactoryMock = $this->getMock(\Magento\SalesRule\Model\CouponFactory::class, ['create'], [], '', false);
        $couponMock = $this->getMock(\Magento\SalesRule\Model\Coupon::class, ['__wakeup'], [], '', false);

        $couponFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($couponMock));
        $salesRuleCouponMock->expects($this->any())
            ->method('getCharset')
            ->with($data['format'])
            ->will($this->returnValue($this->charset));
        $resourceMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

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
