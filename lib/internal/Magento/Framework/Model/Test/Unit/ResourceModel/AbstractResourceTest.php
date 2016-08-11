<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;

class AbstractResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $arguments
     * @param string $expectation
     * @dataProvider serializableFieldsDataProvider
     */
    public function testSerializeFields(array $arguments, $expectation)
    {
        /** @var DataObject $dataObject */
        list($dataObject, $field, $defaultValue, $unsetEmpty) = $arguments;

        $abstractResource = new AbstractResourceStub();

        $abstractResource->_serializeField($dataObject, $field, $defaultValue, $unsetEmpty);

        static::assertEquals($expectation, $dataObject->getDataByKey($field));
    }

    /**
     * @return array
     */
    public function serializableFieldsDataProvider()
    {
        $dataObject = new DataObject(
            [
                'object' => new \stdClass(),
                'array' => ['a', 'b', 'c'],
                'string' => 'i am string',
                'int' => 969,
                'serialized_object' => 'O:8:"stdClass":0:{}',
                'empty_value' => '',
                'empty_value_with_default' => ''
            ]
        );

        return [
            [[$dataObject, 'object', null, false], serialize($dataObject->getDataByKey('object'))],
            [[$dataObject, 'array', null, false], serialize($dataObject->getDataByKey('array'))],
            [[$dataObject, 'string', null, false], serialize($dataObject->getDataByKey('string'))],
            [[$dataObject, 'int', null, false], serialize($dataObject->getDataByKey('int'))],
            [
                [$dataObject, 'serialized_object', null, false],
                serialize($dataObject->getDataByKey('serialized_object'))
            ],
            [[$dataObject, 'empty_value', null, true], null],
            [[$dataObject, 'empty_value_with_default', new \stdClass(), false], 'O:8:"stdClass":0:{}'],
        ];
    }

    /**
     * @param array $arguments
     * @param mixed $expectation
     * @dataProvider unserializableFieldsDataProvider
     */
    public function testUnserializeFields(array $arguments, $expectation)
    {
        /** @var DataObject $dataObject */
        list($dataObject, $field, $defaultValue) = $arguments;

        $abstractResource = new AbstractResourceStub();

        $abstractResource->_unserializeField($dataObject, $field, $defaultValue);

        static::assertEquals($expectation, $dataObject->getDataByKey($field));
    }

    /**
     * @return array
     */
    public function unserializableFieldsDataProvider()
    {
        $dataObject = new DataObject(
            [
                'object' => serialize(new \stdClass()),
                'array' => serialize(['a', 'b', 'c']),
                'string' => serialize('i am string'),
                'int' => serialize(969),
                'serialized_object' => serialize('O:8:"stdClass":0:{}'),
                'empty_value_with_default' => serialize(''),
                'not_serialized_string' => 'i am string',
                'serialized_boolean_false' => serialize(false)
            ]
        );

        $defaultValue = new \stdClass();

        return [
            [[$dataObject, 'object', null], unserialize($dataObject->getDataByKey('object'))],
            [[$dataObject, 'array', null], unserialize($dataObject->getDataByKey('array'))],
            [[$dataObject, 'string', null], unserialize($dataObject->getDataByKey('string'))],
            [[$dataObject, 'int', null], unserialize($dataObject->getDataByKey('int'))],
            [[$dataObject, 'serialized_object', null], unserialize($dataObject->getDataByKey('serialized_object'))],
            [[$dataObject, 'empty_value_with_default', $defaultValue], $defaultValue],
            [[$dataObject, 'not_serialized_string', null], 'i am string'],
            [[$dataObject, 'serialized_boolean_false', null], false]
        ];
    }
    
    public function testCommitZeroLevel()
    {
        /** @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMock(AdapterInterface::class);
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $closureExpectation */
        $closureExpectation = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $abstractResource = new AbstractResourceStub();
        $abstractResource->setConnection($connection);
        $abstractResource->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->setData(1);
            }
        );

        $abstractResource->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->getData();
            }
        );

        $connection->expects(static::once())
            ->method('commit');
        $connection->expects(static::once())
            ->method('getTransactionLevel')
            ->willReturn(0);
        $closureExpectation->expects(static::once())
            ->method('setData')
            ->with(1);
        $closureExpectation->expects(static::once())
            ->method('getData');

        $abstractResource->commit();
    }

    /**
     * @expectedException \Exception
     */
    public function testCommitZeroLevelCallbackException()
    {
        /** @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMock(AdapterInterface::class);

        $abstractResource = new AbstractResourceStub();
        $abstractResource->setConnection($connection);
        $abstractResource->addCommitCallback(
            function () {
                throw new \Exception();
            }
        );

        $connection->expects(static::once())
            ->method('commit');
        $connection->expects(static::once())
            ->method('getTransactionLevel')
            ->willReturn(0);

        $abstractResource->commit();
    }
    
    public function testCommitNotCompletedTransaction()
    {
        /** @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMock(AdapterInterface::class);
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $closureExpectation */
        $closureExpectation = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $abstractResource = new AbstractResourceStub();
        $abstractResource->setConnection($connection);
        $abstractResource->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->setData(1);
            }
        );

        $connection->expects(static::once())
            ->method('commit');
        $connection->expects(static::once())
            ->method('getTransactionLevel')
            ->willReturn(1);

        $closureExpectation->expects(static::never())
            ->method('setData')
            ->with(1);

        $abstractResource->commit();
    }
}
