<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CredentialsValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $objectManagerHelper;
    private $object;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->object = $this->objectManagerHelper
            ->getObject(\Magento\Customer\Model\Customer\CredentialsValidator::class);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Password cannot be the same as email address.
     */
    public function testCheckPasswordDifferentFromEmail()
    {
        $email = 'test1@example.com';
        $password = strtoupper($email); // for case-insensitive check

        $this->object->checkPasswordDifferentFromEmail($email, $password);
    }
}
