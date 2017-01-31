<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class MassAssignGroupTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassAssignGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Adminhtml\Index\MassAssignGroup
     */
    protected $massAction;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerCollectionMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerCollectionFactoryMock;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->contextMock = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $resultRedirectFactory = $this->getMock(
            'Magento\Backend\Model\View\Result\RedirectFactory',
            [],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['create'],
            [],
            '',
            false
        );
        $this->messageManagerMock = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->customerCollectionMock =
            $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionFactoryMock =
            $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\CollectionFactory')
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $redirectMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($redirectMock);

        $this->resultRedirectMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->filterMock = $this->getMock('Magento\Ui\Component\MassAction\Filter', [], [], '', false);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($this->customerCollectionMock)
            ->willReturnArgument(0);
        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerCollectionMock);
        $this->customerRepositoryMock = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->getMockForAbstractClass();
        $this->massAction = $objectManagerHelper->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\MassAssignGroup',
            [
                'context' => $this->contextMock,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->customerCollectionFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
            ]
        );
    }

    public function testExecute()
    {
        $customersIds = [10, 11, 12];
        $customerMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')->getMockForAbstractClass();
        $this->customerCollectionMock->expects($this->any())
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
            ->method('addError')
            ->with('Some message.');

        $this->massAction->execute();
    }
}
