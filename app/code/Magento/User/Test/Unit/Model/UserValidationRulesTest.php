<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Validator\DataObject;
use Magento\User\Model\UserValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for UserValidationRules
 */
class UserValidationRulesTest extends TestCase
{
    /**
     * @var UserValidationRules
     */
    private $userValidationRules;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var DataObject|MockObject
     */
    private $validatorMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->validatorMock = $this->createMock(DataObject::class);

        $this->userValidationRules = new UserValidationRules($this->scopeConfigMock);
    }

    /**
     * Test that configuration value is used when available
     */
    public function testAddPasswordRulesUsesConfigurationValue()
    {
        $configValue = 9;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('admin/security/minimum_password_length')
            ->willReturn($configValue);

        $this->validatorMock->expects($this->exactly(3))
            ->method('addRule')
            ->willReturnSelf();

        $result = $this->userValidationRules->addPasswordRules($this->validatorMock);

        $this->assertSame($this->validatorMock, $result);
    }

    /**
     * Test that fallback value is used when configuration is not set
     */
    public function testAddPasswordRulesUsesFallbackValue()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('admin/security/minimum_password_length')
            ->willReturn(null);

        $this->validatorMock->expects($this->exactly(3))
            ->method('addRule')
            ->willReturnSelf();

        $result = $this->userValidationRules->addPasswordRules($this->validatorMock);

        $this->assertSame($this->validatorMock, $result);
    }

    /**
     * Test that fallback value is used when configuration is empty string
     */
    public function testAddPasswordRulesUsesFallbackValueForEmptyString()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('admin/security/minimum_password_length')
            ->willReturn('');

        $this->validatorMock->expects($this->exactly(3))
            ->method('addRule')
            ->willReturnSelf();

        $result = $this->userValidationRules->addPasswordRules($this->validatorMock);

        $this->assertSame($this->validatorMock, $result);
    }
}
