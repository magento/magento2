<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context as BackendContext;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Adminhtml\Index\MassUnsubscribe;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test mass unsubscribe customers by ids
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassUnsubscribeTest extends TestCase
{
    /**
     * @var MassUnsubscribe
     */
    protected $massAction;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var Manager|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Collection|MockObject
     */
    protected $customerCollectionMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $customerCollectionFactoryMock;

    /**
     * @var Filter|MockObject
     */
    protected $filterMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var SubscriptionManagerInterface|MockObject
     */
    private $subscriptionManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(BackendContext::class);
        $resultRedirectFactory = $this->createMock(RedirectFactory::class);
        $this->responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->createPartialMock(
            ObjectManager::class,
            ['create']
        );
        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->customerCollectionMock =
            $this->getMockBuilder(Collection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->customerCollectionFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirectMock);
        $this->subscriptionManager = $this->getMockForAbstractClass(SubscriptionManagerInterface::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirectMock);

        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultFactoryMock);

        $this->filterMock = $this->createMock(Filter::class);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->customerCollectionMock)
            ->willReturnArgument(0);
        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerCollectionMock);
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->massAction = $objectManagerHelper->getObject(
            MassUnsubscribe::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->customerCollectionFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'subscriptionManager' => $this->subscriptionManager,
            ]
        );
    }

    /**
     * Test to mass unsubscribe customers from newsletters
     */
    public function testExecute()
    {
        $storeId = 2;
        $customerId = 10;
        $customersIds = [$customerId, $customerId, $customerId];

        $this->customerCollectionMock->method('getAllIds')->willReturn($customersIds);
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->method('getStoreId')->willReturn($storeId);
        $customer->method('getId')->willReturn($customerId);
        $this->customerRepositoryMock->method('getById')->with($customerId)->willReturn($customer);
        $this->subscriptionManager->expects($this->exactly(3))
            ->method('unsubscribeCustomer')
            ->with($customerId, $storeId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('A total of %1 record(s) were updated.', count($customersIds)));

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('customer/*/index')
            ->willReturnSelf();

        $this->massAction->execute();
    }

    /**
     * Test to mass unsubscribe customers to newsletters with throws exception
     */
    public function testExecuteWithException()
    {
        $customersIds = [10, 11, 12];

        $this->customerCollectionMock->expects($this->any())
            ->method('getAllIds')
            ->willReturn($customersIds);

        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willThrowException(new \Exception('Some message.'));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('Some message.');

        $this->massAction->execute();
    }
}
