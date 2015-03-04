<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

class EmailNotConfirmedExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $emailNotConfirmedException = new EmailNotConfirmedException(
            new Phrase(
                EmailNotConfirmedException::EMAIL_NOT_CONFIRMED
            )
        );
        $this->assertSame('Email not confirmed', $emailNotConfirmedException->getMessage());
    }
}
