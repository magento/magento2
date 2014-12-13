<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Exception;

class NoSuchEntityExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $exception = new NoSuchEntityException();
        $this->assertEquals('No such entity.', $exception->getRawMessage());
        $this->assertEquals('No such entity.', $exception->getMessage());
        $this->assertEquals('No such entity.', $exception->getLogMessage());

        $exception = new NoSuchEntityException(
            NoSuchEntityException::MESSAGE_SINGLE_FIELD,
            ['fieldName' => 'field', 'fieldValue' => 'value']
        );
        $this->assertEquals('No such entity with field = value', $exception->getMessage());
        $this->assertEquals(NoSuchEntityException::MESSAGE_SINGLE_FIELD, $exception->getRawMessage());
        $this->assertEquals('No such entity with field = value', $exception->getLogMessage());

        $exception = new NoSuchEntityException(
            NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
            [
                'fieldName' => 'field1',
                'fieldValue' => 'value1',
                'field2Name' => 'field2',
                'field2Value' => 'value2'
            ]
        );
        $this->assertEquals(
            NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
            $exception->getRawMessage()
        );
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getMessage());
        $this->assertEquals('No such entity with field1 = value1, field2 = value2', $exception->getLogMessage());
    }
}
