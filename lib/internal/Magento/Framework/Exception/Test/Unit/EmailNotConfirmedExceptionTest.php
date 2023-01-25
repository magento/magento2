<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception\Test\Unit;

use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class EmailNotConfirmedExceptionTest extends TestCase
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
