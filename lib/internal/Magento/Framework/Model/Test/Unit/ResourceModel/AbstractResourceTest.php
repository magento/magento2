<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class AbstractResourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AbstractResourceStub
     */
    private $abstractResource;

    /**
     * @var Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->serializerMock = $this->createMock(Json::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->abstractResource = $objectManager->getObject(AbstractResourceStub::class);
        $objectManager->setBackwardCompatibleProperty($this->abstractResource, 'serializer', $this->serializerMock);
        $objectManager->setBackwardCompatibleProperty($this->abstractResource, '_logger', $this->loggerMock);
    }

    /**
     * @param array $arguments
     * @param string $expected
     * @param array|string|int $serializeCalledWith
     * @param int $numSerializeCalled
     * @dataProvider serializeFieldsDataProvider
     */
    public function testSerializeFields(
        array $arguments,
        $expected,
        $serializeCalledWith,
        $numSerializeCalled = 1
    ) {
        /** @var DataObject $dataObject */
        list($dataObject, $field, $defaultValue, $unsetEmpty) = $arguments;
        $this->serializerMock->expects($this->exactly($numSerializeCalled))
            ->method('serialize')
            ->with($serializeCalledWith)
            ->willReturn($expected);
        $this->abstractResource->_serializeField($dataObject, $field, $defaultValue, $unsetEmpty);
        $this->assertEquals($expected, $dataObject->getData($field));
    }

    /**
     * @return array
     */
    public function serializeFieldsDataProvider()
    {
        $array = ['a', 'b', 'c'];
        $string = 'i am string';
        $integer = 969;
        $empty = '';
        $dataObject = new DataObject(
            [
                'array' => $array,
                'string' => $string,
                'integer' => $integer,
                'empty' => $empty,
                'empty_with_default' => ''
            ]
        );
        return [
            [
                [$dataObject, 'array', null, false],
                '["a","b","c"]',
                $array
            ],
            [
                [$dataObject, 'string', null, false],
                '"i am string"',
                $string
            ],
            [
                [$dataObject, 'integer', null, false],
                '969',
                $integer
            ],
            [
                [$dataObject, 'empty', null, true],
                null,
                $empty,
                0
            ],
            [
                [$dataObject, 'empty_with_default', 'default', false],
                '"default"',
                'default'
            ]
        ];
    }

    /**
     * @param array $arguments
     * @param array|string|int|boolean $expected
     * @dataProvider unserializeFieldsDataProvider
     */
    public function testUnserializeFields(array $arguments, $expected)
    {
        /** @var DataObject $dataObject */
        list($dataObject, $field, $defaultValue) = $arguments;
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($dataObject->getData($field))
            ->willReturn($expected);
        $this->abstractResource->_unserializeField($dataObject, $field, $defaultValue);
        $this->assertEquals($expected, $dataObject->getData($field));
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
                'integer' => '969',
                'empty_with_default' => '""',
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
                [$dataObject, 'integer', null],
                969
            ],
            [
                [$dataObject, 'empty_with_default', 'default', false],
                'default'
            ],
            [
                [$dataObject, 'not_serialized_string', null],
                'i am string'
            ],
            [
                [$dataObject, 'serialized_boolean_false', null],
                false,
            ]
        ];
    }
    
    public function testCommitZeroLevel()
    {
        /** @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject $connection */
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        /** @var DataObject|\PHPUnit\Framework\MockObject\MockObject $closureExpectation */
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

    public function testCommitZeroLevelCallbackException()
    {
        /** @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject $connection */
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);

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
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->abstractResource->commit();
    }

    public function testCommitNotCompletedTransaction()
    {
        /** @var AdapterInterface|\PHPUnit\Framework\MockObject\MockObject $connection */
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        /** @var DataObject|\PHPUnit\Framework\MockObject\MockObject $closureExpectation */
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
