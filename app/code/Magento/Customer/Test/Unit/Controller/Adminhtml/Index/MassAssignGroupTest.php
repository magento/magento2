<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit tests for Magento\Customer\Controller\Adminhtml\Index\MassAssignGroup.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassAssignGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Controller\Adminhtml\Index\MassAssignGroup
     */
    private $massAction;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCollectionMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestInterfaceMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->resultRedirectFactoryMock = $this->createMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        );
        $this->responseMock = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create']
        );
        $this->requestInterfaceMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['isPost']
        );
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->customerCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock = $this->createMock(\Magento\Ui\Component\MassAction\Filter::class);

        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->contextMock->expects($this->once())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->once())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $this->contextMock->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->customerRepositoryMock = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->massAction = $objectManagerHelper->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\MassAssignGroup::class,
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->customerCollectionFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
            ]
        );
    }

    /**
     * Execute Create resultFactory and Create and Get customerCollectionFactory.
     *
     * @return void
     */
    private function expectsCreateAndGetCollectionMethods()
    {
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectMock);
        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerCollectionMock);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->customerCollectionMock)
            ->willReturnArgument(0);
    }

    /**
     * Unit test to verify mass customer group assignment use case.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecute()
    {

        $customersIds = [10, 11, 12];
        $customerMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->expectsCreateAndGetCollectionMethods();
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
        $this->customerCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($customersIds);

        $this->customerRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturnMap([[10, $customerMock], [11, $customerMock], [12, $customerMock]]);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with(__('A total of %1 record(s) were updated.', count($customersIds)));

        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->with('customer/*/index')
            ->willReturnSelf();

        $this->massAction->execute();
    }

    /**
     * Unit test to verify expected error during mass customer group assignment use case.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteWithException()
    {
        $customersIds = [10, 11, 12];
        $this->expectsCreateAndGetCollectionMethods();
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
        $this->customerCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($customersIds);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willThrowException(new \Exception('Some message.'));

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with('Some message.');

        $this->massAction->execute();
    }

    /**
     * Check that error throws when request is not a POST.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteWithNotPostRequest()
    {
        $this->requestMock->expects($this->once())->method('isPost')->willReturn(false);

        $this->massAction->execute();
    }
}
