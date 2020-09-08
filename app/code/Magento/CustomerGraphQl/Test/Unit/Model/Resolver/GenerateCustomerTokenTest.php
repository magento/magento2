<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Resolver;

use PHPUnit\Framework\TestCase;
use Magento\CustomerGraphQl\Model\Resolver\GenerateCustomerToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Test class for \Magento\CustomerGraphQl\Model\Resolver\GenerateCustomerToken
 */
class GenerateCustomerTokenTest extends TestCase
{
    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var RevokeCustomerToken
     */
    private $resolver;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var CustomerTokenServiceInterface|MockObject
     */
    private $customerTokenServiceMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheritdoc
     */

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->customerTokenServiceMock = $this->getMockBuilder(CustomerTokenServiceInterface::class)
            ->getMockForAbstractClass();

        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();

        $this->resolver = $this->objectManager->getObject(
            GenerateCustomerToken::class,
            [
                'customerTokenService' => $this->customerTokenServiceMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testGenerateCustomerToken()
    {
        $generateCustomerTokenResult = 'bkznkdc53qnnxfmscg2szbvakjv7e7pk';
        $customerTokenLifetimeResult = 10;

        $this->customerTokenServiceMock
            ->expects($this->once())
            ->method('createCustomerAccessToken')
            ->willReturn($generateCustomerTokenResult);

        $this->scopeConfigMock->method('getValue')->willReturn($customerTokenLifetimeResult);

        $this->assertEquals(
            [
                'token' => $generateCustomerTokenResult,
                'expiration_time' => $customerTokenLifetimeResult
            ],
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock,
                null,
                [ 'email' => 'test@gmail.com', 'password' => 'Test123@4']
            )
        );
    }
}
