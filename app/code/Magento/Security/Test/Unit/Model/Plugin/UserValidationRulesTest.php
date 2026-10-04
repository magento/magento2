<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\DataObject;
use Magento\Security\Model\Plugin\UserValidationRules;
use Magento\Security\Model\UserExpiration\Validator;
use Magento\User\Model\UserValidationRules as UserModelValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for expiration date user validation rule.
 */
class UserValidationRulesTest extends TestCase
{

    /**
     * @var UserValidationRules|MockObject
     */
    private $plugin;

    /**
     * @var UserModelValidationRules|MockObject
     */
    private $userValidationRules;

    /**
     * @var DataObject|MockObject
     */
    private $validator;

    /**
     * @var UserModelValidationRules
     */
    private $rules;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $userExpirationValidator = $this->createMock(Validator::class);
        $this->userValidationRules = $this->createMock(UserModelValidationRules::class);
        $this->rules = $objectManager->getObject(UserModelValidationRules::class);
        $this->validator = $this->createMock(DataObject::class);
        $this->plugin =
            $objectManager->getObject(
                UserValidationRules::class,
                ['validator' => $userExpirationValidator]
            );
    }

    public function testAfterAddUserInfoRules()
    {
        $this->validator->expects(static::exactly(5))->method('addRule')->willReturn($this->validator);
        static::assertSame($this->validator, $this->rules->addUserInfoRules($this->validator));
        static::assertSame($this->validator, $this->callAfterAddUserInfoRulesPlugin($this->validator));
    }

    protected function callAfterAddUserInfoRulesPlugin($validator)
    {
        return $this->plugin->afterAddUserInfoRules($this->userValidationRules, $validator);
    }
}
