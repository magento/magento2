<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\Plugin;

/**
 * Test class for expiration date user validation rule.
 */
class UserValidationRulesTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Security\Model\Plugin\UserValidationRules|\PHPUnit\Framework\MockObject\MockObject
     */
    private $plugin;

    /**
     * @var \Magento\User\Model\UserValidationRules|\PHPUnit\Framework\MockObject\MockObject
     */
    private $userValidationRules;

    /**
     * @var \Magento\Framework\Validator\DataObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validator;

    /**
     * @var \Magento\User\Model\UserValidationRules
     */
    private $rules;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $userExpirationValidator = $this->createMock(\Magento\Security\Model\UserExpiration\Validator::class);
        $this->userValidationRules = $this->createMock(\Magento\User\Model\UserValidationRules::class);
        $this->rules = $objectManager->getObject(\Magento\User\Model\UserValidationRules::class);
        $this->validator = $this->createMock(\Magento\Framework\Validator\DataObject::class);
        $this->plugin =
            $objectManager->getObject(
                \Magento\Security\Model\Plugin\UserValidationRules::class,
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
