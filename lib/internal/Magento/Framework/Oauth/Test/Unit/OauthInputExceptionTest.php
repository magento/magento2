<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth\Test\Unit;

use \Magento\Framework\Oauth\OauthInputException;
use Magento\Framework\Phrase;

class OauthInputExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testGetAggregatedErrorMessage()
    {
        $exception = new OauthInputException();
        foreach (['field1', 'field2'] as $param) {
            $exception->addError(new Phrase('%fieldName is a required field.', ['fieldName' => $param]));
        }
        $exception->addError(new Phrase('Message with period.'));

        $this->assertEquals(
            'field1 is a required field, field2 is a required field, Message with period',
            $exception->getAggregatedErrorMessage()
        );
    }

    /**
     * @return void
     */
    public function testGetAggregatedErrorMessageNoError()
    {
        $exception = new OauthInputException();
        $this->assertEquals('', $exception->getAggregatedErrorMessage());
    }
}
