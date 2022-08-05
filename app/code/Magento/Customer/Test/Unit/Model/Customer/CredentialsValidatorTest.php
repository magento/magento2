<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class CredentialsValidatorTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CredentialsValidator
     */
    private $object;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->object = $this->objectManagerHelper
            ->getObject(CredentialsValidator::class);
    }

    public function testCheckPasswordDifferentFromEmail()
    {
        $this->expectException(InputException::class);

        $email = 'test1@example.com';
        $password = strtoupper($email); // for case-insensitive check

        $this->object->checkPasswordDifferentFromEmail($email, $password);

        $this->expectExceptionMessage(
            "The password can't be the same as the email address. Create a new password and try again."
        );
    }
}
