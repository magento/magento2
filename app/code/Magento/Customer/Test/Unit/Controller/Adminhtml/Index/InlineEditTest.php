<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Message\MessageInterface;

/**
 * Unit tests for Inline customer edit
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEditTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\Controller\Adminhtml\Index\InlineEdit */
    private $controller;

    /** @var \Magento\Backend\App\Action\Context */
    private $context;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $request;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $messageManager;

    /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject*/
    protected $customerData;

    /** @var \Magento\Customer\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $address;

    /** @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject*/
    private $resultJsonFactory;

    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject*/
    private $resultJson;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $customerRepository;

    /** @var \Magento\Customer\Model\Address\Mapper|\PHPUnit_Framework_MockObject_MockObject*/
    private $addressMapper;

    /** @var \Magento\Customer\Model\Customer\Mapper|\PHPUnit_Framework_MockObject_MockObject*/
    private $customerMapper;

    /** @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject*/
    private $dataObjectHelper;

    /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject*/
    private $addressDataFactory;

    /** @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $addressRepository;

    /** @var \Magento\Framework\Message\Collection|\PHPUnit_Framework_MockObject_MockObject*/
    private $messageCollection;

    /** @var \Magento\Framework\Message\MessageInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $message;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject*/
    private $logger;

    /** @var EmailNotificationInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $emailNotification;

    /** @var AddressRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $addressRegistry;

    /** @var array */
    private $items;

    /**
     * Sets up mocks
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false
        );
        $this->messageManager = $this->getMockForAbstractClass(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->customerData = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            '',
            false
        );
        $this->address = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            'address',
            false
        );
        $this->addressMapper = $this->createMock(\Magento\Customer\Model\Address\Mapper::class);
        $this->customerMapper = $this->createMock(\Magento\Customer\Model\Customer\Mapper::class);
        $this->resultJsonFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create']
        );
        $this->resultJson = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->customerRepository = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->addressDataFactory = $this->createPartialMock(
            \Magento\Customer\Api\Data\AddressInterfaceFactory::class,
            ['create']
        );
        $this->addressRepository = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AddressRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->messageCollection = $this->createMock(\Magento\Framework\Message\Collection::class);
        $this->message = $this->getMockForAbstractClass(
            \Magento\Framework\Message\MessageInterface::class,
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(
            \Psr\Log\LoggerInterface::class,
            [],
            '',
            false
        );
        $this->emailNotification = $this->getMockBuilder(EmailNotificationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager,
            ]
        );
        $this->addressRegistry = $this->createMock(\Magento\Customer\Model\AddressRegistry::class);
        $this->controller = $objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\InlineEdit::class,
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
                'addressRegistry' => $this->addressRegistry
            ]
        );
        $reflection = new \ReflectionClass(get_class($this->controller));
        $reflectionProperty = $reflection->getProperty('emailNotification');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->controller, $this->emailNotification);

        $this->items = [
            14 => [
                'email' => 'test@test.ua',
                'billing_postcode' => '07294',
            ]
        ];
    }

    /**
     * Prepare mocks for tests
     *
     * @param int $populateSequence
     */
    protected function prepareMocksForTesting($populateSequence = 0)
    {
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('items', [])
            ->willReturn($this->items);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with(14)
            ->willReturn($this->customerData);
        $this->customerMapper->expects($this->once())
            ->method('toFlatArray')
            ->with($this->customerData)
            ->willReturn(['name' => 'Firstname Lastname']);
        $this->dataObjectHelper->expects($this->at($populateSequence))
            ->method('populateWithArray')
            ->with(
                $this->customerData,
                [
                    'name' => 'Firstname Lastname',
                    'email' => 'test@test.ua',
                ],
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
        $this->customerData->expects($this->any())
            ->method('getId')
            ->willReturn(12);
    }

    /**
     * Prepare mocks for update customers default billing address use case
     */
    protected function prepareMocksForUpdateDefaultBilling()
    {
        $this->prepareMocksForProcessAddressData();
        $addressData = [
            'postcode' => '07294',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
        ];
        $this->customerData->expects($this->exactly(2))
            ->method('getAddresses')
            ->willReturn([$this->address]);
        $this->address->expects($this->once())
            ->method('isDefaultBilling')
            ->willReturn(true);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->willReturn(new DataObject());
        $this->dataObjectHelper->expects($this->at(0))
            ->method('populateWithArray')
            ->with(
                $this->address,
                $addressData,
                \Magento\Customer\Api\Data\AddressInterface::class
            );
    }

    /**
     * Prepare mocks for processing customers address data use case
     */
    protected function prepareMocksForProcessAddressData()
    {
        $this->customerData->expects($this->once())
            ->method('getFirstname')
            ->willReturn('Firstname');
        $this->customerData->expects($this->once())
            ->method('getLastname')
            ->willReturn('Lastname');
    }

    /**
     * Prepare mocks for error messages processing test
     */
    protected function prepareMocksForErrorMessagesProcessing()
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
            ->with([
                'messages' => ['Error text'],
                'error' => true,
            ])
            ->willReturnSelf();
    }

    /**
     * Unit test for updating customers billing address use case
     */
    public function testExecuteWithUpdateBilling()
    {
        $this->prepareMocksForTesting(1);
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
     * Unit test for creating customer with empty data use case
     */
    public function testExecuteWithoutItems()
    {
        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('items', [])
            ->willReturn([]);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(false);
        $this->resultJson
            ->expects($this->once())
            ->method('setData')
            ->with([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ])
            ->willReturnSelf();
        $this->assertSame($this->resultJson, $this->controller->execute());
    }

    /**
     * Unit test for verifying Localized Exception during inline edit
     */
    public function testExecuteLocalizedException()
    {
        $exception = new \Magento\Framework\Exception\LocalizedException(__('Exception message'));
        $this->prepareMocksForTesting();
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
     * Unit test for verifying Execute Exception during inline edit
     */
    public function testExecuteException()
    {
        $exception = new \Exception('Exception message');
        $this->prepareMocksForTesting();
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
