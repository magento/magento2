<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\Framework\Model\ResourceModel\AbstractResource.
 */
class AbstractResourceTest extends TestCase
{
    /**
     * @var AbstractResourceStub
     */
    private $model;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->serializerMock = $this->createMock(Json::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->model = $objectManager->getObject(AbstractResourceStub::class);
        $objectManager->setBackwardCompatibleProperty($this->model, 'serializer', $this->serializerMock);
        $objectManager->setBackwardCompatibleProperty($this->model, '_logger', $this->loggerMock);
    }

    /**
     * Test fields serialize
     *
     * @param array $arguments
     * @param string|null $expected
     * @param array|string|int $serializeCalledWith
     * @param int $numSerializeCalled
     * @return void
     * @dataProvider serializeFieldsDataProvider
     */
    public function testSerializeFields(
        array $arguments,
        ?string $expected,
        $serializeCalledWith,
        int $numSerializeCalled = 1
    ): void {
        /** @var DataObject $dataObject */
        [$dataObject, $field, $defaultValue, $unsetEmpty] = $arguments;
        $this->serializerMock->expects($this->exactly($numSerializeCalled))
            ->method('serialize')
            ->with($serializeCalledWith)
            ->willReturn($expected);
        $this->model->_serializeField($dataObject, $field, $defaultValue, $unsetEmpty);
        $this->assertEquals($expected, $dataObject->getData($field));
    }

    /**
     * DataProvider for testSerializeFields()
     *
     * @return array
     */
    public function serializeFieldsDataProvider(): array
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
                'empty_with_default' => '',
            ]
        );

        return [
            [
                [$dataObject, 'array', null, false],
                '["a","b","c"]',
                $array,
            ],
            [
                [$dataObject, 'string', null, false],
                '"i am string"',
                $string,
            ],
            [
                [$dataObject, 'integer', null, false],
                '969',
                $integer,
            ],
            [
                [$dataObject, 'empty', null, true],
                null,
                $empty,
                0,
            ],
            [
                [$dataObject, 'empty_with_default', 'default', false],
                '"default"',
                'default',
            ],
        ];
    }

    /**
     * Test fields unserialize
     *
     * @param array $arguments
     * @param array|string|int|boolean $expected
     * @return void
     * @dataProvider unserializeFieldsDataProvider
     */
    public function testUnserializeFields(array $arguments, $expected): void
    {
        /** @var DataObject $dataObject */
        [$dataObject, $field, $defaultValue] = $arguments;
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($dataObject->getData($field))
            ->willReturn($expected);
        $this->model->_unserializeField($dataObject, $field, $defaultValue);
        $this->assertEquals($expected, $dataObject->getData($field));
    }

    /**
     * DataProvider for testUnserializeFields()
     *
     * @return array
     */
    public function unserializeFieldsDataProvider(): array
    {
        $dataObject = new DataObject(
            [
                'array' => '["a","b","c"]',
                'string' => '"i am string"',
                'integer' => '969',
                'empty_with_default' => '""',
                'not_serialized_string' => 'i am string',
                'serialized_boolean_false' => 'false',
            ]
        );

        return [
            [
                [$dataObject, 'array', null],
                ['a', 'b', 'c'],
            ],
            [
                [$dataObject, 'string', null],
                'i am string',
            ],
            [
                [$dataObject, 'integer', null],
                969,
            ],
            [
                [$dataObject, 'empty_with_default', 'default', false],
                'default',
            ],
            [
                [$dataObject, 'not_serialized_string', null],
                'i am string',
            ],
            [
                [$dataObject, 'serialized_boolean_false', null],
                false,
            ],
        ];
    }

    /**
     * Commit zero level
     *
     * @return void
     */
    public function testCommitZeroLevel(): void
    {
        /** @var AdapterInterface|MockObject $connection */
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        /** @var DataObject|MockObject $closureExpectation */
        $closureExpectation = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setConnection($connection);
        $this->model->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->setData(1);
            }
        );

        $this->model->addCommitCallback(
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

        $this->model->commit();
    }

    /**
     * Commit zero level callback with exception
     *
     * @return void
     */
    public function testCommitZeroLevelCallbackException(): void
    {
        /** @var AdapterInterface|MockObject $connection */
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->model->setConnection($connection);
        $this->model->addCommitCallback(
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

        $this->model->commit();
    }

    /**
     * Commit of transactions that have not been completed
     *
     * @return void
     */
    public function testCommitNotCompletedTransaction(): void
    {
        /** @var AdapterInterface|MockObject $connection */
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        /** @var DataObject|MockObject $closureExpectation */
        $closureExpectation = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setConnection($connection);
        $this->model->addCommitCallback(
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

        $this->model->commit();
    }

    /**
     * Test commit case when first callback throws an exception but other callbacks will be called
     *
     * @return void
     */
    public function testCommitFewCallbacksWithException(): void
    {
        /** @var AdapterInterface|MockObject $connection */
        $connection = $this->createMock(AdapterInterface::class);

        /** @var DataObject|MockObject $closureExpectation */
        $closureExpectation = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setConnection($connection);
        $this->model->addCommitCallback(
            function () {
                throw new \Exception();
            }
        );

        $this->model->addCommitCallback(
            function () use ($closureExpectation) {
                $closureExpectation->getData();
            }
        );

        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->once())
            ->method('getTransactionLevel')
            ->willReturn(0);
        $this->loggerMock->expects($this->once())
            ->method('critical');
        $closureExpectation->expects($this->once())
            ->method('getData');

        $this->model->commit();
    }
}
