<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Oauth;

class OauthInputExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAggregatedErrorMessage()
    {
        $exception = new OauthInputException();
        foreach (['field1', 'field2'] as $param) {
            $exception->addError(OauthInputException::REQUIRED_FIELD, ['fieldName' => $param]);
        }
        $exception->addError('Message with period.', ['fieldName' => 'field3']);

        $this->assertEquals(
            'field1 is a required field, field2 is a required field, Message with period',
            $exception->getAggregatedErrorMessage()
        );
    }

    public function testGetAggregatedErrorMessageNoError()
    {
        $exception = new OauthInputException();
        $this->assertEquals('', $exception->getAggregatedErrorMessage());
    }
}
