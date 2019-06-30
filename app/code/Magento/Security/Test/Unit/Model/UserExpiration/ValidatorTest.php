<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\UserExpiration;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ExpiresAtValidatorTest
 *
 * @package Magento\User\Test\Unit\Model
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Security\Model\UserExpiration\Validator
     */
    private $validator;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(\Magento\Security\Model\UserExpiration\Validator::class);
    }

    public function testWithPastDate()
    {
        $testDate = new \DateTime();
        $testDate->modify('-10 days');
        static::assertFalse($this->validator->isValid($testDate->format('Y-m-d H:i:s')));
        static::assertContains(
            '"Expiration date" must be later than the current date.',
            $this->validator->getMessages()
        );
    }

    public function testWithFutureDate()
    {
        $testDate = new \DateTime();
        $testDate->modify('+10 days');
        static::assertTrue($this->validator->isValid($testDate->format('Y-m-d H:i:s')));
        static::assertEquals([], $this->validator->getMessages());
    }
}
