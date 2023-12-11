<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Model;

use Magento\Framework\Validator\DataObject;
use Magento\User\Model\UserValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserValidationRulesTest extends TestCase
{
    /**
     * @var DataObject|MockObject
     */
    private $validator;

    /**
     * @var UserValidationRules
     */
    private $rules;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(DataObject::class);
        $this->rules = new UserValidationRules();
    }

    public function testAddUserInfoRules()
    {
        $this->validator->expects($this->exactly(4))->method('addRule')->willReturn($this->validator);
        $this->assertSame($this->validator, $this->rules->addUserInfoRules($this->validator));
    }

    public function testAddPasswordRules()
    {
        $this->validator->expects($this->exactly(3))->method('addRule')->willReturn($this->validator);
        $this->assertSame($this->validator, $this->rules->addPasswordRules($this->validator));
    }

    public function testAddPasswordConfirmationRule()
    {
        $this->validator->expects($this->once())->method('addRule')->willReturn($this->validator);
        $this->assertSame($this->validator, $this->rules->addPasswordConfirmationRule($this->validator, ''));
    }
}
