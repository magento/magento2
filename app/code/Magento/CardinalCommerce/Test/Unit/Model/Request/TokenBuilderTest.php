<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Test\Unit\Model\Request;

use Magento\CardinalCommerce\Model\Request\TokenBuilder;
use Magento\CardinalCommerce\Model\JwtManagement;
use Magento\CardinalCommerce\Model\Config;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Cardinal request token builder
 */
class TokenBuilderTest extends TestCase
{
    private const API_KEY = 'API key';
    private const ORDER_ID = '125';
    private const GRANDTOTAL = '150';
    private const CURRENCY_CODE = 'USD';

    /**
     * @var TokenBuilder
     */
    private $model;

    /**
     * @var JwtManagement|MockObject
     */
    private $jwtManagementMock;

    /**
     * @var Session|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var IdentityGeneratorInterface|MockObject
     */
    private $identityGeneratorMock;

    /**
     * @var DateTimeFactory|MockObject
     */
    private $dateTimeFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->jwtManagementMock = $this->createMock(JwtManagement::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->configMock = $this->createMock(Config::class);
        $this->identityGeneratorMock = $this->createMock(IdentityGeneratorInterface::class);
        $this->dateTimeFactoryMock = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            TokenBuilder::class,
            [
                'jwtManagement' => $this->jwtManagementMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'config' => $this->configMock,
                'identityGenerator' => $this->identityGeneratorMock,
                'dateTimeFactory' => $this->dateTimeFactoryMock
            ]
        );
    }

    /**
     * Test build()
     *
     * @param array $payload
     * @param string $jwt
     * @return void
     * @dataProvider dataProviderForBulid
     */
    public function testBuild($payload, $jwt): void
    {

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getBaseGrandTotal', 'getBaseCurrencyCode'])
            ->getMock();

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::ORDER_ID);
        $quoteMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn(self::GRANDTOTAL);
        $quoteMock->expects($this->once())
            ->method('getBaseCurrencyCode')
            ->willReturn(self::CURRENCY_CODE);

        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $dateTime = new \DateTime(
            '2015-12-01 19:24:25',
            new \DateTimeZone('UTC')
        );
        $this->dateTimeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($dateTime);

        $this->identityGeneratorMock->expects($this->once())
            ->method('generateId')
            ->willReturn($payload['jti']);

        $this->configMock->expects($this->once())
            ->method('getApiIdentifier')
            ->willReturn($payload['iss']);
        $this->configMock->expects($this->once())
            ->method('getOrgUnitId')
            ->willReturn($payload['OrgUnitId']);
        $this->configMock->expects($this->once())
            ->method('getApiKey')
            ->willReturn(self::API_KEY);

        $this->jwtManagementMock->expects($this->once())
            ->method('encode')
            ->with($payload, self::API_KEY)
            ->willReturn($jwt);

        $this->assertEquals($jwt, $this->model->build());
    }

    /**
     * Data provider for bulid()
     */
    public function dataProviderForBulid(): array
    {
        return [
            [
                'payload' => [
                    'jti' => 'a5a59bfb-ac06-4c5f-be5c-351b64ae608e',
                    'iss' => '56560a358b946e0c8452365ds',
                    'iat' => '1448997865',
                    'OrgUnitId' => '565607c18b946e058463ds8r',
                    'Payload' => [
                        'OrderDetails' => [
                            'OrderNumber' => self::ORDER_ID,
                            'Amount' => self::GRANDTOTAL * 100,
                            'CurrencyCode' => self::CURRENCY_CODE
                        ]
                    ],
                    'ObjectifyPayload' => true
                ],
                'valid jwt signed using HS256' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiJhNWE1OWJmYi1h' .
                    'YzA2LTRjNWYtYmU1Yy0zNTFiNjRhZTYwOGUiLCJpc3MiOiI1NjU2MGEzNThiOTQ2ZTBjODQ1MjM2NWRzIiwiaWF0Ijoi' .
                    'MTQ0ODk5Nzg2NSIsIk9yZ1VuaXRJZCI6IjU2NTYwN2MxOGI5NDZlMDU4NDYzZHM4ciIsIlBheWxvYWQiOnsiT3JkZXJE' .
                    'ZXRhaWxzIjp7Ik9yZGVyTnVtYmVyIjoiMTI1IiwiQW1vdW50IjoiMTUwMCIsIkN1cnJlbmN5Q29kZSI6IlVTRCJ9fSwi' .
                    'T2JqZWN0aWZ5UGF5bG9hZCI6dHJ1ZX0.emv9N75JIvyk_gQHMNJYQ2UzmbM5ISBQs1Y222zO1jk'
            ]
        ];
    }
}
