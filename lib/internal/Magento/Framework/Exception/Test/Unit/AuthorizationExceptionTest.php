<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception\Test\Unit;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class AuthorizationExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $authorizationException = new AuthorizationException(
            new Phrase(
                'The consumer isn\'t authorized to access %resources.',
                ['consumer_id' => 1, 'resources' => 'record2']
            )
        );
        $this->assertSame("The consumer isn't authorized to access record2.", $authorizationException->getMessage());
    }
}
