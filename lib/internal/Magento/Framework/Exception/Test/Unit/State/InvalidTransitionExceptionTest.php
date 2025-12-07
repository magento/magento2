<?php
/**
 * Invalid state exception
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception\Test\Unit\State;

use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class InvalidTransitionExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $instanceClass = InvalidTransitionException::class;
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $stateException = new InvalidTransitionException(new Phrase($message, $params), $cause);
        $this->assertInstanceOf($instanceClass, $stateException);
    }
}
