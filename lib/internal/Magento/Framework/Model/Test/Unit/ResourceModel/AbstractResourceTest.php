<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

class AbstractResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractResourceStub
     */
    private $abstractResource;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->serializerMock = $this->getMock(SerializerInterface::class);
        $this->abstractResource = $objectManager->getObject(AbstractResourceStub::class);
        $objectManager->setBackwardCompatibleProperty(
            $this->abstractResource,
            'serializer',
            $this->serializerMock
        );
    }

    /**
     * @param array $arguments
     * @param string $expectation
     * @param int $numSerializeCalled
     * @dataProvider serializeFieldsDataProvider
     */
    public function testSerializeFields(array $arguments, $expectation, $numSerializeCalled = 1)
    {
        /** @var DataObject $dataObject */
        list($dataObject, $field, $defaultValue, $unsetEmpty) = $arguments;
        $this->serializerMock->expects($this->exactly($numSerializeCalled))
            ->method('serialize')
            ->with($dataObject->getData($field))
            ->willReturn($expectation);
        $this->abstractResource->_serializeField($dataObject, $field, $defaultValue, $unsetEmpty);
        $this->assertEquals($expectation, $dataObject->getData($field));
    }

    /**
     * @return array
     */
    public function serializeFieldsDataProvider()
    {
        $dataObject = new DataObject(
            [
                'array' => ['a', 'b', 'c'],
                'string' => 'i am string',
                'int' => 969,
                'empty_value' => '',
                'empty_value_with_default' => ''
            ]
        );
        return [
            [
                [$dataObject, 'array', null, false],
                '["a","b","c"]'
            ],
            [
                [$dataObject, 'string', null, false],
                '"i am string"'
            ],
            [
                [$dataObject, 'int', null, false],
                '969'
            ],
            [
                [$dataObject, 'empty_value', null, true],
                null,
                0
            ]
        ];
    }

    /**
     * @param array $arguments
     * @param mixed $expectation
     * @dataProvider unserializeFieldsDataProvider
     */
    public function testUnserializeFields(array $arguments, $expectation)
    {
        /** @var DataObject $dataObject */
        list($dataObject, $field, $defaultValue) = $arguments;
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($dataObject->getData($field))
            ->willReturn($expectation);
        $this->abstractResource->_unserializeField($dataObject, $field, $defaultValue);
        $this->assertEquals($expectation, $dataObject->getData($field));
    }

    /**
     * @return array
     */
    public function unserializeFieldsDataProvider()
    {
        $dataObject = new DataObject(
            [
                'array' => '["a","b","c"]',
                'string' => '"i am string"',
                'int' => '969',
                'empty_value_with_default' => '""',
                'not_serialized_string' => 'i am string',
                'serialized_boolean_false' => 'false'
            ]
        );
        return [
            [
                [$dataObject, 'array', null],
                ['a', 'b', 'c']
            ],
            [
                [$dataObject, 'string', null],
                'i am string'
            ],
            [
                [$dataObject, 'int', null],
                969
            ]
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

        $this->abstractResource->setConnection($connection);
        $this->abstractResource->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->setData(1);
            }
        );

        $this->abstractResource->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->getData();
            }
        );

        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(0);
        $closureExpectation->expects($this->once())
            ->method('setData')
            ->with(1);
        $closureExpectation->expects($this->once())
            ->method('getData');

        $this->abstractResource->commit();
    }

    /**
     * @expectedException \Exception
     */
    public function testCommitZeroLevelCallbackException()
    {
        /** @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMock(AdapterInterface::class);

        $this->abstractResource->setConnection($connection);
        $this->abstractResource->addCommitCallback(
            function () {
                throw new \Exception();
            }
        );

        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(0);

        $this->abstractResource->commit();
    }
    
    public function testCommitNotCompletedTransaction()
    {
        /** @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMock(AdapterInterface::class);
        /** @var DataObject|\PHPUnit_Framework_MockObject_MockObject $closureExpectation */
        $closureExpectation = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->abstractResource->setConnection($connection);
        $this->abstractResource->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->setData(1);
            }
        );

        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(1);

        $closureExpectation->expects($this->never())
            ->method('setData')
            ->with(1);

        $this->abstractResource->commit();
    }
}
