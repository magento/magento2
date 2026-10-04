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
use Magento\Customer\Model\EmailNotificationInterface;
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPostTest extends TestCase
{
    use MockCreationTrait;

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
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                EmailNotificationInterface::class,
                $this->createMock(EmailNotificationInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->context = $this->createMock(Context::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->accountManagement = $this->createMock(AccountManagementInterface::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->formKeyValidator = $this->createMock(Validator::class);
        $this->customerExtractor = $this->createMock(CustomerExtractor::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->addressRegistry = $this->createMock(AddressRegistry::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->sessionCleaner = $this->createMock(SessionCleanerInterface::class);
        $this->accountConfirmation = $this->createMock(AccountConfirmation::class);
        $this->customerUrl = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMapper = $this->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
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

        $eventManager = $this->createMock(EventManagerInterface::class);
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);

        $messageManager = $this->createMock(MessageManagerInterface::class);
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

        $customer = $this->createMock(CustomerInterface::class);
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
        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnCallback(
                function ($arg) use ($attr) {
                    if ($arg == 'change_email') {
                        return false;
                    } elseif ($arg == 'delete_attribute_value') {
                        return $attr;
                    } elseif ($arg == $attr . File::UPLOADED_FILE_SUFFIX) {
                        return 'uploadedFileName';
                    }
                }
            );

        $this->editPost->execute();
    }
}
