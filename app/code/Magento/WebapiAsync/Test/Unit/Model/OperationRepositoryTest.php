<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
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
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->messageValidatorMock = $this->createMock(MessageValidator::class);
        $this->jsonSerializerMock = $this->createMock(Json::class);
        $this->inputParamsResolverMock = $this->createMock(InputParamsResolver::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->messageEncoderMock = $this->createMock(MessageEncoder::class);
        $this->operationMock = $this->createMock(OperationInterface::class);
        $this->storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);

        $this->operationRepository = new OperationRepository(
            $this->operationFactoryMock,
            $this->entityManagerMock,
            $this->messageValidatorMock,
            $this->jsonSerializerMock,
            $this->inputParamsResolverMock,
            $this->storeManagerMock,
            $this->messageEncoderMock
        );
    }

    /**
     * Test that OperationRepository uses MessageEncoder with resolved entityParams
     */
    public function testCreateUsesMessageEncoderWithResolvedParams()
    {
        $topicName = 'sales.refund.invoice';
        $groupId = 'test-group-id';
        $operationId = 1;
        $storeId = 1;

        // Resolved entity params already converted by ServiceInputProcessor
        $resolvedEntityParams = [
            'invoiceId' => 123,
            'items' => [],
            'isOnline' => true,  // camelCase, boolean
            'notify' => false,
            'appendComment' => false,
        ];

        // Raw input data with snake_case as received from API
        $rawInputData = [
            $operationId => [
                'items' => [],
                'is_online' => true,  // snake_case, string
                'notify' => false,
            ],
        ];

        $encodedMessage = '{"invoiceId":123,"items":[],"isOnline":true,"notify":false,"appendComment":false}';
        $serializedData = [
            'entity_id' => null,
            'entity_link' => '',
            'meta_information' => $encodedMessage,
            'store_id' => $storeId,
        ];
        $serializedDataJson = json_encode($serializedData);

        $this->inputParamsResolverMock->expects($this->once())
            ->method('getInputData')
            ->willReturn($rawInputData);

        $this->messageValidatorMock->expects($this->once())
            ->method('validate')
            ->with($topicName, $resolvedEntityParams);

        $this->messageEncoderMock->expects($this->once())
            ->method('encode')
            ->with($topicName, $resolvedEntityParams)
            ->willReturn($encodedMessage);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $this->jsonSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($serializedData)
            ->willReturn($serializedDataJson);

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
     * Test that OperationRepository validates operationId exists in input data
     */
    public function testCreateThrowsExceptionWhenOperationIdNotFound()
    {
        $topicName = 'sales.refund.invoice';
        $groupId = 'test-group-id';
        $operationId = 999; // non-existent operation ID
        $resolvedEntityParams = ['invoiceId' => 123];

        $rawInputData = [
            1 => ['items' => []], // different operation ID
        ];

        $this->inputParamsResolverMock->expects($this->once())
            ->method('getInputData')
            ->willReturn($rawInputData);

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
            $operationId => ['items' => []],
        ];

        $encodedMessage = '{"invoiceId":123}';
        $serializedData = [
            'entity_id' => null,
            'entity_link' => '',
            'meta_information' => $encodedMessage,
        ];
        $serializedDataJson = json_encode($serializedData);

        $this->inputParamsResolverMock->expects($this->once())
            ->method('getInputData')
            ->willReturn($rawInputData);

        $this->messageValidatorMock->expects($this->once())
            ->method('validate')
            ->with($topicName, $resolvedEntityParams);

        $this->messageEncoderMock->expects($this->once())
            ->method('encode')
            ->with($topicName, $resolvedEntityParams)
            ->willReturn($encodedMessage);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException(new NoSuchEntityException(__('Store not found')));

        $this->jsonSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($serializedData)
            ->willReturn($serializedDataJson);

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
}
