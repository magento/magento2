<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ExpiresAtValidatorTest
 * @package Magento\User\Test\Unit\Model
 */
class ExpiresAtTest extends \PHPUnit\Framework\TestCase
{

    /** @var \Magento\User\Model\Validator\ExpiresAt */
    protected $validator;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->validator = $objectManager->getObject(
            \Magento\User\Model\Validator\ExpiresAt::class
        );
    }

    public function testIsValidWhenInvalid()
    {
        static::assertFalse($this->validator->isValid('2018-01-01 00:00:00'));
        static::assertContains(
            'The expiration date must be later than the current date.',
            $this->validator->getMessages()
        );
    }

    public function testIsValidWhenValid()
    {
        $futureDate = new \DateTime();
        $futureDate->modify('+1 days');
        static::assertTrue($this->validator->isValid($futureDate->format('Y-m-d H:i:s')));
        static::assertEquals([], $this->validator->getMessages());
    }
}
