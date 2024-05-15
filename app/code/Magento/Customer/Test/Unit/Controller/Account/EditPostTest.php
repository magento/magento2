<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Account;

use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Account\EditPost;
use Magento\Customer\Model\Metadata\Form\File;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\Url;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPostTest extends TestCase
{
    /**
     * @var EditPost
     */
    private $editPost;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var AccountManagementInterface|MockObject
     */
    private $accountManagement;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidator;

    /**
     * @var CustomerExtractor|MockObject
     */
    private $customerExtractor;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var AddressRegistry|MockObject
     */
    private $addressRegistry;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var SessionCleanerInterface|MockObject
     */
    private $sessionCleaner;

    /**
     * @var AccountConfirmation|MockObject
     */
    private $accountConfirmation;

    /**
     * @var Url|MockObject
     */
    private $customerUrl;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Mapper|MockObject
     */
    private $customerMapper;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accountManagement = $this->getMockBuilder(AccountManagementInterface::class)
            ->getMockForAbstractClass();
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerExtractor = $this->getMockBuilder(CustomerExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRegistry = $this->getMockBuilder(AddressRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionCleaner = $this->getMockBuilder(SessionCleanerInterface::class)
            ->getMockForAbstractClass();
        $this->accountConfirmation = $this->getMockBuilder(AccountConfirmation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerUrl = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMapper = $this->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['isPost', 'getPostValue'])
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $redirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($redirect);

        $eventManager = $this->getMockBuilder(EventManagerInterface::class)
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);

        $messageManager = $this->getMockBuilder(MessageManagerInterface::class)
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($messageManager);

        $this->editPost = new EditPost(
            $this->context,
            $this->customerSession,
            $this->accountManagement,
            $this->customerRepository,
            $this->formKeyValidator,
            $this->customerExtractor,
            $this->escaper,
            $this->addressRegistry,
            $this->filesystem,
            $this->sessionCleaner,
            $this->accountConfirmation,
            $this->customerUrl,
            $this->customerMapper
        );
    }

    /**
     * @return void
     * @throws SessionException
     */
    public function testExecute()
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->getMockForAbstractClass();
        $customer->expects($this->any())
            ->method('getAddresses')
            ->willReturn([]);
        $this->customerRepository->expects($this->any())
            ->method('getById')
            ->willReturn($customer);

        $this->customerMapper->expects($this->once())
            ->method('toFlatArray')
            ->willReturn([]);
        $this->customerExtractor->expects($this->once())
            ->method('extract')
            ->willReturn($customer);

        $attr = 'attr1';
        $this->request->expects($this->exactly(5))
            ->method('getParam')
            ->withConsecutive(
                ['change_email'],
                [ 'delete_attribute_value'],
                [$attr . File::UPLOADED_FILE_SUFFIX]
            )->willReturnOnConsecutiveCalls(
                false,
                $attr,
                'uploadedFileName'
            );

        $this->editPost->execute();
    }
}
