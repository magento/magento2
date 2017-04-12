<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Phrase;

/**
 * Class EmailNotConfirmedExceptionTest
 */
class EmailNotConfirmedExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $emailNotConfirmedException = new EmailNotConfirmedException(
            new Phrase(
                'Email not confirmed',
                ['consumer_id' => 1, 'resources' => 'record2']
            )
        );
        $this->assertSame('Email not confirmed', $emailNotConfirmedException->getMessage());
    }
}
