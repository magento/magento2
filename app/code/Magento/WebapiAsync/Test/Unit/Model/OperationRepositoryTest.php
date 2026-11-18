<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\WebapiAsync\Controller\Rest\Asynchronous\InputParamsResolver;
use Magento\WebapiAsync\Model\OperationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for OperationRepository
 */
class OperationRepositoryTest extends TestCase
{
    /**
     * @var OperationRepository
     */
    private $operationRepository;

    /**
     * @var OperationInterfaceFactory|MockObject
     */
    private $operationFactoryMock;

    /**
     * @var EntityManager|MockObject
     */
    private $entityManagerMock;

    /**
     * @var MessageValidator|MockObject
     */
    private $messageValidatorMock;

    /**
     * @var Json|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var InputParamsResolver|MockObject
     */
    private $inputParamsResolverMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var MessageEncoder|MockObject
     */
    private $messageEncoderMock;

    /**
     * @var CommunicationConfig|MockObject
     */
    private $communicationConfigMock;

    /**
     * @var OperationInterface|MockObject
     */
    private $operationMock;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->operationFactoryMock = $this->createMock(OperationInterfaceFactory::class);
        $this->entityManagerMock = $this->createStub(EntityManager::class);
        $this->messageValidatorMock = $this->createMock(MessageValidator::class);
        $this->jsonSerializerMock = $this->createStub(Json::class);
        $this->inputParamsResolverMock = $this->createMock(InputParamsResolver::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->messageEncoderMock = $this->createMock(MessageEncoder::class);
        $this->communicationConfigMock = $this->createMock(CommunicationConfig::class);
        $this->operationMock = $this->createStub(OperationInterface::class);
        $this->storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);

        $this->jsonSerializerMock->method('serialize')->willReturnCallback(
            static function ($value) {
                return json_encode($value);
            }
        );

        $this->operationRepository = new OperationRepository(
            $this->operationFactoryMock,
            $this->entityManagerMock,
            $this->messageValidatorMock,
            $this->jsonSerializerMock,
            $this->inputParamsResolverMock,
            $this->storeManagerMock,
            $this->messageEncoderMock,
            $this->communicationConfigMock
        );
    }

    /**
     * Omitted product status must stay omitted, including getter defaults such as stock_item.
     */
    public function testCreateOmitsProductFieldsThatWereNotSubmitted()
    {
        $payload = $this->createAndReadPayload(
            'async.magento.catalog.api.productrepositoryinterface.save',
            [
                'product' => [
                    'sku' => 'async-status-repro',
                    'weight' => 2.5,
                ],
            ],
            [
                $this->methodParameter('product', 0, true),
                $this->methodParameter('saveOptions', 1, false),
            ],
            ['product' => 'resolved-product']
        );

        $this->assertSame(
            [
                'sku' => 'async-status-repro',
                'weight' => 2.5,
            ],
            $payload['product']
        );
        $this->assertArrayNotHasKey('status', $payload['product']);
        $this->assertArrayNotHasKey('stock_item', $payload['product']);
        $this->assertArrayNotHasKey('status', $payload);
    }

    /**
     * Snake_case top-level keys must match the method parameter name before the message is stored.
     *
     * Without that rename, MessageEncoder::decode() ignores is_online and the refund runs offline.
     */
    public function testCreateRenamesTopLevelSnakeCaseToMethodParameterName()
    {
        $submitted = [
            'invoiceId' => 123,
            'items' => [],
            'is_online' => true,
            'notify' => false,
        ];
        $this->assertArrayNotHasKey('isOnline', $submitted);

        $payload = $this->createAndReadPayload(
            'async.magento.sales.api.refundinvoiceinterface.execute',
            $submitted,
            [
                $this->methodParameter('invoiceId', 0, true),
                $this->methodParameter('items', 1, false),
                $this->methodParameter('isOnline', 2, false),
                $this->methodParameter('notify', 3, false),
                $this->methodParameter('appendComment', 4, false),
            ],
            ['invoiceId' => 123, 'isOnline' => true]
        );

        $this->assertTrue($payload['isOnline']);
        $this->assertArrayNotHasKey('is_online', $payload);
        $this->assertSame(123, $payload['invoiceId']);
        $this->assertFalse($payload['notify']);
    }

    /**
     * A payload that already uses the method parameter names is stored unchanged.
     */
    public function testCreateLeavesCamelCaseTopLevelParametersUnchanged()
    {
        $submitted = [
            'invoiceId' => 123,
            'items' => [],
            'isOnline' => true,
            'notify' => false,
        ];

        $payload = $this->createAndReadPayload(
            'async.magento.sales.api.refundinvoiceinterface.execute',
            $submitted,
            [
                $this->methodParameter('invoiceId', 0, true),
                $this->methodParameter('items', 1, false),
                $this->methodParameter('isOnline', 2, false),
                $this->methodParameter('notify', 3, false),
            ],
            ['invoiceId' => 123, 'isOnline' => true]
        );

        $this->assertSame($submitted, $payload);
    }

    /**
     * Test that OperationRepository validates operationId exists in input data
     */
    public function testCreateThrowsExceptionWhenOperationIdNotFound()
    {
        $topicName = 'sales.refund.invoice';
        $groupId = 'test-group-id';
        $operationId = 999;
        $resolvedEntityParams = ['invoiceId' => 123];

        $rawInputData = [
            1 => ['items' => []],
        ];

        $this->inputParamsResolverMock->expects($this->once())
            ->method('getInputData')
            ->willReturn($rawInputData);
        $this->operationFactoryMock->expects($this->never())->method('create');
        $this->messageValidatorMock->expects($this->never())->method('validate');
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->messageEncoderMock->expects($this->never())->method('encode');
        $this->communicationConfigMock->expects($this->never())->method('getTopic');
        $this->storeMock->expects($this->never())->method('getId');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "$operationId" must not be NULL and must exist in input data');

        $this->operationRepository->create(
            $topicName,
            $resolvedEntityParams,
            $groupId,
            $operationId
        );
    }

    /**
     * Test that OperationRepository handles missing store gracefully
     */
    public function testCreateHandlesMissingStore()
    {
        $topicName = 'sales.refund.invoice';
        $groupId = 'test-group-id';
        $operationId = 1;
        $resolvedEntityParams = ['invoiceId' => 123];

        $rawInputData = [
            $operationId => ['invoiceId' => 123],
        ];

        $this->inputParamsResolverMock->expects($this->once())
            ->method('getInputData')
            ->willReturn($rawInputData);

        $this->communicationConfigMock->expects($this->once())
            ->method('getTopic')
            ->with($topicName)
            ->willReturn([
                CommunicationConfig::TOPIC_REQUEST => [
                    $this->methodParameter('invoiceId', 0, true),
                ],
            ]);

        $this->messageValidatorMock->expects($this->once())
            ->method('validate')
            ->with($topicName, $resolvedEntityParams);

        $this->messageEncoderMock->expects($this->never())
            ->method('encode');

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException(new NoSuchEntityException(__('Store not found')));
        $this->storeMock->expects($this->never())->method('getId');

        $this->operationFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->operationMock);

        $result = $this->operationRepository->create(
            $topicName,
            $resolvedEntityParams,
            $groupId,
            $operationId
        );

        $this->assertSame($this->operationMock, $result);
    }

    /**
     * Store one operation payload and return the decoded meta_information.
     *
     * @param string $topicName
     * @param array $rawItem
     * @param array $schema
     * @param array $entityParams
     * @return array
     */
    private function createAndReadPayload(
        string $topicName,
        array $rawItem,
        array $schema,
        array $entityParams
    ): array {
        $operationId = 0;
        $this->inputParamsResolverMock->expects($this->once())
            ->method('getInputData')
            ->willReturn([$operationId => $rawItem]);

        $this->communicationConfigMock->expects($this->once())
            ->method('getTopic')
            ->with($topicName)
            ->willReturn([
                CommunicationConfig::TOPIC_REQUEST => $schema,
            ]);

        $this->messageValidatorMock->expects($this->once())
            ->method('validate')
            ->with($topicName, $entityParams);

        $this->messageEncoderMock->expects($this->never())
            ->method('encode');

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $captured = null;
        $this->operationFactoryMock->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (array $data) use (&$captured) {
                $captured = $data;
                return $this->operationMock;
            });

        $result = $this->operationRepository->create($topicName, $entityParams, 'group-id', $operationId);
        $this->assertSame($this->operationMock, $result);

        $serializedData = json_decode($captured['data'][OperationInterface::SERIALIZED_DATA], true);
        return json_decode($serializedData['meta_information'], true);
    }

    /**
     * @param string $name
     * @param int $position
     * @param bool $required
     * @return array
     */
    private function methodParameter(string $name, int $position, bool $required): array
    {
        return [
            CommunicationConfig::SCHEMA_METHOD_PARAM_NAME => $name,
            CommunicationConfig::SCHEMA_METHOD_PARAM_POSITION => $position,
            CommunicationConfig::SCHEMA_METHOD_PARAM_IS_REQUIRED => $required,
            CommunicationConfig::SCHEMA_METHOD_PARAM_TYPE => 'mixed',
        ];
    }
}
