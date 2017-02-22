<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

class NoSuchEntityExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $exception = new NoSuchEntityException();
        $this->assertEquals('No such entity.', $exception->getRawMessage());
        $this->assertEquals('No such entity.', $exception->getMessage());
        $this->assertEquals('No such entity.', $exception->getLogMessage());

        $exception = new NoSuchEntityException(
            new Phrase(
                NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                ['fieldName' => 'field', 'fieldValue' => 'value']
            )
        );
        $this->assertEquals('No such entity with field = value', $exception->getMessage());
        $this->assertEquals(NoSuchEntityException::MESSAGE_SINGLE_FIELD, $exception->getRawMessage());
        $this->assertEquals('No such entity with field = value', $exception->getLogMessage());

        $exception = new NoSuchEntityException(
            new Phrase(
                NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                [
                    'fieldName' => 'field1',
                    'fieldValue' => 'value1',
                    'field2Name' => 'field2',
                    'field2Value' => 'value2'
                ]
            )
        );
        $this->assertEquals(
            NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
            $exception->getRawMessage()
        );
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getMessage());
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getLogMessage());
    }
}
