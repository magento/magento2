<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception\Test\Unit;

use \Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;

class InputExceptionTest extends \PHPUnit\Framework\TestCase
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
            new Phrase('The %fieldName value of "%value" must be greater than or equal to %minValue.', $params)
        );

        $this->assertSame(
            'The %fieldName value of "%value" must be greater than or equal to %minValue.',
            $inputException->getRawMessage()
        );
        $this->assertStringMatchesFormat('%s greater than or equal to %s', $inputException->getMessage());
        $this->assertSame(
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

        $this->assertSame('One or more input exceptions have occurred.', $inputException->getRawMessage());
        $this->assertSame(
            'One or more input exceptions have occurred.',
            $inputException->getMessage()
        );
        $this->assertSame('One or more input exceptions have occurred.', $inputException->getLogMessage());

        $this->assertFalse($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $inputException->addError(
            new Phrase(
                'The %fieldName value of "%value" must be greater than or equal to %minValue.',
                ['fieldName' => 'weight', 'value' => -100, 'minValue' => 1]
            )
        );
        $this->assertTrue($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $this->assertSame(
            'The %fieldName value of "%value" must be greater than or equal to %minValue.',
            $inputException->getRawMessage()
        );
        $this->assertSame(
            'The weight value of "-100" must be greater than or equal to 1.',
            $inputException->getMessage()
        );
        $this->assertSame(
            'The weight value of "-100" must be greater than or equal to 1.',
            $inputException->getLogMessage()
        );

        $inputException->addError(
            new Phrase('"%fieldName" is required. Enter and try again.', ['fieldName' => 'name'])
        );
        $this->assertTrue($inputException->wasErrorAdded());
        $this->assertCount(2, $inputException->getErrors());

        $this->assertSame('One or more input exceptions have occurred.', $inputException->getRawMessage());
        $this->assertSame(
            'One or more input exceptions have occurred.',
            $inputException->getMessage()
        );
        $this->assertSame('One or more input exceptions have occurred.', $inputException->getLogMessage());

        $errors = $inputException->getErrors();
        $this->assertCount(2, $errors);

        $this->assertSame(
            'The %fieldName value of "%value" must be greater than or equal to %minValue.',
            $errors[0]->getRawMessage()
        );
        $this->assertSame(
            'The weight value of "-100" must be greater than or equal to 1.',
            $errors[0]->getMessage()
        );
        $this->assertSame(
            'The weight value of "-100" must be greater than or equal to 1.',
            $errors[0]->getLogMessage()
        );

        $this->assertSame('"%fieldName" is required. Enter and try again.', $errors[1]->getRawMessage());
        $this->assertSame('"name" is required. Enter and try again.', $errors[1]->getMessage());
        $this->assertSame('"name" is required. Enter and try again.', $errors[1]->getLogMessage());
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
        $this->assertSame($rawMessage, $inputException->getRawMessage());
        $this->assertSame($expectedProcessedMessage, $inputException->getMessage());
        $this->assertSame($expectedProcessedMessage, $inputException->getLogMessage());
        $this->assertFalse($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $inputException->addError(new Phrase($rawMessage, $params));
        $this->assertSame($expectedProcessedMessage, $inputException->getMessage());
        $this->assertSame($expectedProcessedMessage, $inputException->getLogMessage());
        $this->assertTrue($inputException->wasErrorAdded());
        $this->assertCount(0, $inputException->getErrors());

        $inputException->addError(new Phrase($rawMessage, $params));
        $this->assertSame($expectedProcessedMessage, $inputException->getMessage());
        $this->assertSame($expectedProcessedMessage, $inputException->getLogMessage());
        $this->assertTrue($inputException->wasErrorAdded());

        $errors = $inputException->getErrors();
        $this->assertCount(2, $errors);
        $this->assertSame($expectedProcessedMessage, $errors[0]->getMessage());
        $this->assertSame($expectedProcessedMessage, $errors[0]->getLogMessage());
        $this->assertSame($expectedProcessedMessage, $errors[1]->getMessage());
        $this->assertSame($expectedProcessedMessage, $errors[1]->getLogMessage());
    }
}
