<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
                'No such entity with %fieldName = %fieldValue',
                ['fieldName' => 'field', 'fieldValue' => 'value']
            )
        );
        $this->assertEquals('No such entity with field = value', $exception->getMessage());
        $this->assertEquals('No such entity with %fieldName = %fieldValue', $exception->getRawMessage());
        $this->assertEquals('No such entity with field = value', $exception->getLogMessage());

        $exception = new NoSuchEntityException(
            new Phrase(
                'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                [
                    'fieldName' => 'field1',
                    'fieldValue' => 'value1',
                    'field2Name' => 'field2',
                    'field2Value' => 'value2'
                ]
            )
        );
        $this->assertEquals(
            'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
            $exception->getRawMessage()
        );
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getMessage());
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getLogMessage());
    }
}
