<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;

class InputExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verify that the constructor creates a single instance of InputException with the proper
     * message and array of parameters.
     *
     * @return void
     */
    public function testConstructor()
    {
        $params = ['fieldName' => 'quantity', 'value' => -100, 'minValue' => 0];
        $inputException = new InputException(
            new Phrase(InputException::INVALID_FIELD_MIN_VALUE, $params)
        );

        $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $inputException->getRawMessage());
        $this->assertStringMatchesFormat('%s greater than or equal to %s', $inputException->getMessage());
        $this->assertEquals(
            'The quantity value of "-100" must be greater than or equal to 0.',
            $inputException->getLogMessage()
        );
    }

    /**
     * Verify that adding multiple errors works correctly.
     *
     * @return void
     */
    public function testAddError()
    {
        $inputException = new InputException();

        $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getRawMessage());
        $this->assertEquals(
            InputException::DEFAULT_MESSAGE,
            $inputException->getMessage()
        );
        $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getLogMessage());

        $this->assertFalse($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $inputException->addError(
            new Phrase(
                InputException::INVALID_FIELD_MIN_VALUE,
                ['fieldName' => 'weight', 'value' => -100, 'minValue' => 1]
            )
        );
        $this->assertTrue($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $inputException->getRawMessage());
        $this->assertEquals(
            'The weight value of "-100" must be greater than or equal to 1.',
            $inputException->getMessage()
        );
        $this->assertEquals(
            'The weight value of "-100" must be greater than or equal to 1.',
            $inputException->getLogMessage()
        );

        $inputException->addError(new Phrase(InputException::REQUIRED_FIELD, ['fieldName' => 'name']));
        $this->assertTrue($inputException->wasErrorAdded());
        $this->assertCount(2, $inputException->getErrors());

        $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getRawMessage());
        $this->assertEquals(
            InputException::DEFAULT_MESSAGE,
            $inputException->getMessage()
        );
        $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getLogMessage());

        $errors = $inputException->getErrors();
        $this->assertCount(2, $errors);

        $this->assertEquals(InputException::INVALID_FIELD_MIN_VALUE, $errors[0]->getRawMessage());
        $this->assertEquals(
            'The weight value of "-100" must be greater than or equal to 1.',
            $errors[0]->getMessage()
        );
        $this->assertEquals(
            'The weight value of "-100" must be greater than or equal to 1.',
            $errors[0]->getLogMessage()
        );

        $this->assertEquals(InputException::REQUIRED_FIELD, $errors[1]->getRawMessage());
        $this->assertEquals('name is a required field.', $errors[1]->getMessage());
        $this->assertEquals('name is a required field.', $errors[1]->getLogMessage());
    }

    /**
     * Verify the message and params are not used to determine the call count
     *
     * @return void
     */
    public function testAddErrorWithSameMessage()
    {
        $rawMessage = 'Foo "%var"';
        $params = ['var' => 'Bar'];
        $expectedProcessedMessage = 'Foo "Bar"';
        $inputException = new InputException(new Phrase($rawMessage, $params));
        $this->assertEquals($rawMessage, $inputException->getRawMessage());
        $this->assertEquals($expectedProcessedMessage, $inputException->getMessage());
        $this->assertEquals($expectedProcessedMessage, $inputException->getLogMessage());
        $this->assertFalse($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $inputException->addError(new Phrase($rawMessage, $params));
        $this->assertEquals($expectedProcessedMessage, $inputException->getMessage());
        $this->assertEquals($expectedProcessedMessage, $inputException->getLogMessage());
        $this->assertTrue($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $inputException->addError(new Phrase($rawMessage, $params));
        $this->assertEquals($expectedProcessedMessage, $inputException->getMessage());
        $this->assertEquals($expectedProcessedMessage, $inputException->getLogMessage());
        $this->assertTrue($inputException->wasErrorAdded());

        $errors = $inputException->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals($expectedProcessedMessage, $errors[0]->getMessage());
        $this->assertEquals($expectedProcessedMessage, $errors[0]->getLogMessage());
        $this->assertEquals($expectedProcessedMessage, $errors[1]->getMessage());
        $this->assertEquals($expectedProcessedMessage, $errors[1]->getLogMessage());
    }
}
