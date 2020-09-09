<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception\Test\Unit;

use Magento\Framework\Exception\StateException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class StateExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testStateExceptionInstance()
    {
        $instanceClass = StateException::class;
        $message = 'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $stateException = new StateException(new Phrase($message, $params), $cause);
        $this->assertInstanceOf($instanceClass, $stateException);
    }
}
