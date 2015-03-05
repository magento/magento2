<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\EmailNotConfirmedException;

/**
 * Class EmailNotConfirmedExceptionTest
 *
 * @package Magento\Framework\Exception
 */
class EmailNotConfirmedExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $emailNotConfirmedException = new EmailNotConfirmedException(
            EmailNotConfirmedException::EMAIL_NOT_CONFIRMED,
            ['consumer_id' => 1, 'resources' => 'record2']
        );
        $this->assertSame('Email not confirmed', $emailNotConfirmedException->getMessage());
    }
}
