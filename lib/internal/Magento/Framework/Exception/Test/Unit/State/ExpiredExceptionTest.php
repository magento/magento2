<?php
/**
 * Expired exception
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Exception\Test\Unit\State;

use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\TestCase;

class ExpiredExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $instanceClass = ExpiredException::class;
        $message =  'message %1 %2';
        $params = [
            'parameter1',
            'parameter2',
        ];
        $cause = new \Exception();
        $stateException = new ExpiredException(new Phrase($message, $params), $cause);
        $this->assertInstanceOf($instanceClass, $stateException);
    }
}
