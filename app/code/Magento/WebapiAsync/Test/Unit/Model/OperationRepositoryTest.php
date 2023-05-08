<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Bulk\OperationInterface as OperationInterfaceAlias;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\WebapiAsync\Model\OperationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Model\Data\Customer;
use Magento\WebapiAsync\Controller\Rest\Asynchronous\InputParamsResolver;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OperationRepositoryTest extends TestCase
{
    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var OperationInterfaceFactory|MockObject
     */
    private $operationFactoryMock;

    /**
     * @var Json|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var MessageValidator|MockObject
     */
    private $messageValidatorMock;

    /**
     * @var EntityManager|MockObject
     */
    private $entityManagerMock;

    /**
     * @var InputParamsResolver|MockObject
     */
    private $inputParamsResolverMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private $authorizationMock;

    /**
     * @var WebApiAsyncConfig|MockObject
     */
    private $webapiAsyncConfigMock;

    /**
     * @var OperationRepository
     */
    private $operation;

    protected function setUp(): void
    {
        $this->customerMock = $this->createMock(Customer::class);
        $this->operationFactoryMock = $this->createPartialMock(
            OperationInterfaceFactory::class,
            ['create']
        );
        $this->jsonSerializerMock = $this->createMock(Json::class);
        $this->messageValidatorMock = $this->getMockBuilder(MessageValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputParamsResolverMock = $this->getMockBuilder(InputParamsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->webapiAsyncConfigMock = $this->getMockBuilder(WebApiAsyncConfig::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->operation = new OperationRepository(
            $this->operationFactoryMock,
            $this->entityManagerMock,
            $this->messageValidatorMock,
            $this->jsonSerializerMock,
            $this->inputParamsResolverMock,
            $this->storeManagerMock,
            $this->authorizationMock,
            $this->webapiAsyncConfigMock
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [3, true, 3],
            [2, false, 1]
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $inputGroupId
     * @param $isAllowed
     * @param $expectedGroupId
     * @return void
     */
    public function testCreate($inputGroupId, $isAllowed, $expectedGroupId): void
    {
        $topicName = "async.magento.customer.api.accountmanagementinterface.createaccount.post";
        $entityParams = [
            $this->customerMock,
            "Password1",
            ""
        ];
        $groupId = "13b44977-7579-421f-a432-85bbcfbafc64";
        $operationId = 0;
        $requestData = [
            0 => [
                'customer' => [
                    'lastname' => 'Doe',
                    'firstname' => 'Jane',
                    'email' => 'test@gmail.com',
                    'group_id' => $inputGroupId,
                    'addresses' => []
                ],
                'password' => 'Password1'
            ]
        ];

        if (!$isAllowed) {
            $requestData[$operationId]['customer']['group_id'] = $expectedGroupId;
        }
        $this->messageValidatorMock->expects($this->once())->method('validate')->willReturn(false);
        $this->inputParamsResolverMock->expects($this->once())->method('getInputData')->willReturn($requestData);

        $this->webapiAsyncConfigMock->expects($this->once())->method('getTopicName')
            ->with('V1/customers', 'POST')
            ->willReturn($topicName);

        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Customer::create')
            ->willReturn($isAllowed);
        $serializedData = [
            'entity_id'        => null,
            'entity_link'      => '',
            'meta_information' => json_encode($requestData[$operationId]),
            'store_id' => 1
        ];
        if ($isAllowed) {
            $serializedData['isAsyncAuthorized'] = 1;
        }
        $this->jsonSerializerMock->expects($this->exactly(2))
            ->method('serialize')
            ->withConsecutive([$requestData[$operationId]], [$serializedData])
            ->willReturnOnConsecutiveCalls(json_encode($requestData[$operationId]), $serializedData);

        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->method('getId')->willReturn(1);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $data = [
            'data' => [
                OperationInterfaceAlias::ID => $operationId,
                OperationInterfaceAlias::BULK_ID => $groupId,
                OperationInterfaceAlias::TOPIC_NAME => $topicName,
                OperationInterfaceAlias::SERIALIZED_DATA => $serializedData,
                OperationInterfaceAlias::STATUS => OperationInterfaceAlias::STATUS_TYPE_OPEN,
            ],
        ];
        $operation = $this->getMockBuilder(OperationInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $operation->expects($this->once())->method('getData')->willReturn(json_encode($requestData[$operationId]));
        $this->operationFactoryMock->method('create')->with($data)->willReturn($operation);

        $result = $this->operation->create($topicName, $entityParams, $groupId, $operationId);
        $decode = json_decode($result->getData());
        $this->assertEquals($expectedGroupId, $decode->customer->group_id);
    }
}
