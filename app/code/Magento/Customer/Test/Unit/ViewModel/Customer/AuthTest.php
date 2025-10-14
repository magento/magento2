<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\ViewModel\Customer;

use Magento\Customer\ViewModel\Customer\Auth;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private mixed $contextMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private mixed $scopeConfigMock;

    /**
     * @var Auth
     */
    private Auth $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Auth(
            $this->contextMock,
            $this->scopeConfigMock
        );
        parent::setUp();
    }

    /**
     * Test is logged in value.
     *
     * @return void
     */
    public function testIsLoggedIn(): void
    {
        $this->contextMock->expects($this->once())
            ->method('getValue')
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->model->isLoggedIn()
        );
    }

    /**
     * @dataProvider getCustomerShareScopeDataProvider
     */
    public function testGetCustomerShareScope($configValue, int $expected): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'customer/account_share/scope',
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
            ->willReturn($configValue);

        $this->assertSame($expected, $this->model->getCustomerShareScope());
    }

    /**
     * @return array
     */
    public function getCustomerShareScopeDataProvider(): array
    {
        return [
            'global scope as string 0' => ['0', 0],
            'website scope as string 1' => ['1', 1],
            'null value defaults to 0' => [null, 0],
            'empty string defaults to 0' => ['', 0],
        ];
    }
}
