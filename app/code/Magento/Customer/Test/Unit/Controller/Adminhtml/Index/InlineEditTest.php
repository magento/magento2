<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Adminhtml\Index\InlineEdit;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for Inline customer edit
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEditTest extends TestCase
{
    /**
     * @var InlineEdit
     */
    private $controller;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var CustomerInterface|MockObject
     */
    protected $customerData;

    /**
     * @var AddressInterface|MockObject
     */
    private $address;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactory;

    /**
     * @var Json|MockObject
     */
    private $resultJson;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var Mapper|MockObject
     */
    private $addressMapper;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper|MockObject
     */
    private $customerMapper;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var AddressInterfaceFactory|MockObject
     */
    private $addressDataFactory;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepository;

    /**
     * @var Collection|MockObject
     */
    private $messageCollection;

    /**
     * @var MessageInterface|MockObject
     */
    private $message;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var EmailNotificationInterface|MockObject
     */
    private $emailNotification;

    /**
     * @var AddressRegistry|MockObject
     */
    private $addressRegistry;

    /**
     * @var array
     */
    private $items;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->escaper = new Escaper();
        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false
        );
        $this->messageManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false
        );
        $this->customerData = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            false
        );
        $this->address = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            'address',
            false
        );
        $this->addressMapper = $this->createMock(Mapper::class);
        $this->customerMapper = $this->createMock(\Magento\Customer\Model\Customer\Mapper::class);
        $this->resultJsonFactory = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->resultJson = $this->createMock(Json::class);
        $this->customerRepository = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->addressDataFactory = $this->createPartialMock(
            AddressInterfaceFactory::class,
            ['create']
        );
        $this->addressRepository = $this->getMockForAbstractClass(
            AddressRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->messageCollection = $this->createMock(Collection::class);
        $this->message = $this->getMockForAbstractClass(
            MessageInterface::class,
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            '',
            false
        );
        $this->emailNotification = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager,
            ]
        );
        $this->addressRegistry = $this->createMock(AddressRegistry::class);
        $this->controller = $objectManager->getObject(
            InlineEdit::class,
            [
                'context' => $this->context,
                'resultJsonFactory' => $this->resultJsonFactory,
                'customerRepository' => $this->customerRepository,
                'addressMapper' => $this->addressMapper,
                'customerMapper' => $this->customerMapper,
                'dataObjectHelper' => $this->dataObjectHelper,
                'addressDataFactory' => $this->addressDataFactory,
                'addressRepository' => $this->addressRepository,
                'logger' => $this->logger,
                'addressRegistry' => $this->addressRegistry,
                'escaper' => $this->escaper
            ]
        );
        $reflection = new \ReflectionClass(get_class($this->controller));
        $reflectionProperty = $reflection->getProperty('emailNotification');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->controller, $this->emailNotification);

        $this->items = [
            14 => [
                'email' => 'test@test.ua',
                'billing_postcode' => '07294'
            ]
        ];
    }

    /**
     * Prepare mocks for tests.
     *
     * @return void
     */
    protected function prepareMocksForTesting(): void
    {
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request
            ->method('getParam')
            ->withConsecutive(['items', []], ['isAjax'])
            ->willReturnOnConsecutiveCalls($this->items, true);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with(14)
            ->willReturn($this->customerData);
        $this->customerMapper->expects($this->once())
            ->method('toFlatArray')
            ->with($this->customerData)
            ->willReturn(['name' => 'Firstname Lastname']);
        $this->customerData->expects($this->any())
            ->method('getId')
            ->willReturn(12);
    }

    /**
     * Prepare mocks for update customers default billing address use case.
     *
     * @return void
     */
    protected function prepareMocksForUpdateDefaultBilling(): void
    {
        $this->prepareMocksForProcessAddressData();
        $this->customerData->expects($this->exactly(2))
            ->method('getAddresses')
            ->willReturn([$this->address]);
        $this->address->expects($this->once())
            ->method('isDefaultBilling')
            ->willReturn(true);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->willReturn(new DataObject());
    }

    /**
     * Prepare mocks for processing customers address data use case.
     *
     * @return void
     */
    protected function prepareMocksForProcessAddressData(): void
    {
        $this->customerData->expects($this->once())
            ->method('getFirstname')
            ->willReturn('Firstname');
        $this->customerData->expects($this->once())
            ->method('getLastname')
            ->willReturn('Lastname');
    }

    /**
     * Prepare mocks for error messages processing test.
     *
     * @return void
     */
    protected function prepareMocksForErrorMessagesProcessing(): void
    {
        $this->messageManager->expects($this->atLeastOnce())
            ->method('getMessages')
            ->willReturn($this->messageCollection);
        $this->messageCollection->expects($this->once())
            ->method('getErrors')
            ->willReturn([$this->message]);
        $this->messageCollection->expects($this->once())
            ->method('getCountByType')
            ->with(MessageInterface::TYPE_ERROR)
            ->willReturn(1);
        $this->message->expects($this->once())
            ->method('getText')
            ->willReturn('Error text');
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        'Error text'
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();
    }

    /**
     * Unit test for updating customers billing address use case.
     *
     * @return void
     */
    public function testExecuteWithUpdateBilling(): void
    {
        $this->prepareMocksForTesting();
        $this->dataObjectHelper
            ->method('populateWithArray')
            ->withConsecutive(
                [
                    $this->address,
                    [
                        'postcode' => '07294',
                        'firstname' => 'Firstname',
                        'lastname' => 'Lastname'
                    ],
                    AddressInterface::class
                ],
                [
                    $this->customerData,
                    [
                        'name' => 'Firstname Lastname',
                        'email' => 'test@test.ua'
                    ],
                    CustomerInterface::class
                ]
            );

        $this->customerData->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn(23);

        $this->prepareMocksForUpdateDefaultBilling();
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($this->customerData);

        $this->emailNotification->expects($this->once())
            ->method('credentialsChanged')
            ->willReturnSelf();

        $this->prepareMocksForErrorMessagesProcessing();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Unit test for creating customer with empty data use case.
     *
     * @return void
     */
    public function testExecuteWithoutItems(): void
    {
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request
            ->method('getParam')
            ->withConsecutive(['items', []], ['isAjax'])
            ->willReturnOnConsecutiveCalls([], false);
        $this->resultJson
            ->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'messages' => [
                        __('Please correct the data sent.')
                    ],
                    'error' => true
                ]
            )
            ->willReturnSelf();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Unit test for verifying Localized Exception during inline edit.
     *
     * @return void
     */
    public function testExecuteLocalizedException(): void
    {
        $exception = new LocalizedException(__('Exception message'));
        $this->prepareMocksForTesting();
        $this->dataObjectHelper
            ->method('populateWithArray')
            ->withConsecutive(
                [
                    $this->customerData,
                    [
                        'name' => 'Firstname Lastname',
                        'email' => 'test@test.ua'
                    ],
                    CustomerInterface::class
                ]
            );

        $this->customerData->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn(false);
        $this->customerData->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($this->customerData)
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('[Customer ID: 12] Exception message');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->prepareMocksForErrorMessagesProcessing();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Unit test for verifying Execute Exception during inline edit.
     *
     * @return void
     */
    public function testExecuteException(): void
    {
        $exception = new \Exception('Exception message');
        $this->prepareMocksForTesting();
        $this->dataObjectHelper
            ->method('populateWithArray')
            ->withConsecutive(
                [
                    $this->customerData,
                    [
                        'name' => 'Firstname Lastname',
                        'email' => 'test@test.ua'
                    ],
                    CustomerInterface::class
                ]
            );
        $this->customerData->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn(false);
        $this->customerData->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($this->customerData)
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('[Customer ID: 12] We can\'t save the customer.');
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->prepareMocksForErrorMessagesProcessing();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }
}
