<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Address\FormPost;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormPostTest extends TestCase
{
    /**
     * @var FormPost
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var FormKeyValidator|MockObject
     */
    protected $formKeyValidator;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactory;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    protected $addressRepository;

    /**
     * @var AddressInterfaceFactory|MockObject
     */
    protected $addressDataFactory;

    /**
     * @var RegionInterfaceFactory|MockObject
     */
    protected $regionDataFactory;

    /**
     * @var DataObjectProcessor|MockObject
     */
    protected $dataProcessor;

    /**
     * @var DataObjectHelper|MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactory;

    /**
     * @var PageFactory|MockObject
     */
    protected $resultPageFactory;

    /**
     * @var RegionFactory|MockObject
     */
    protected $regionFactory;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResultRedirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirect;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var AddressInterface|MockObject
     */
    protected $addressData;

    /**
     * @var RegionInterface|MockObject
     */
    protected $regionData;

    /**
     * @var Form|MockObject
     */
    protected $form;

    /**
     * @var HelperData|MockObject
     */
    protected $helperData;

    /**
     * @var Region|MockObject
     */
    protected $region;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var Mapper|MockObject
     */
    private $customerAddressMapper;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->prepareContext();

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setAddressFormData',
                'getCustomerId',
            ])
            ->getMock();

        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareForm();
        $this->prepareAddress();
        $this->prepareRegion();

        $this->dataProcessor = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperData = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerAddressMapper = $this->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new FormPost(
            $this->context,
            $this->session,
            $this->formKeyValidator,
            $this->formFactory,
            $this->addressRepository,
            $this->addressDataFactory,
            $this->regionDataFactory,
            $this->dataProcessor,
            $this->dataObjectHelper,
            $this->resultForwardFactory,
            $this->resultPageFactory,
            $this->regionFactory,
            $this->helperData
        );

        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'customerAddressMapper',
            $this->customerAddressMapper
        );
    }

    /**
     * Prepares context
     */
    protected function prepareContext(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods([
                'isPost',
                'getPostValue',
                'getParam',
            ])
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->redirect = $this->getMockBuilder(RedirectInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
    }

    /**
     * Prepare address
     */
    protected function prepareAddress(): void
    {
        $this->addressRepository = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->addressData = $this->getMockBuilder(AddressInterface::class)
            ->getMockForAbstractClass();

        $this->addressDataFactory = $this->getMockBuilder(AddressInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'create',
            ])
            ->getMock();
        $this->addressDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->addressData);
    }

    /**
     * Prepare region
     */
    protected function prepareRegion(): void
    {
        $this->region = $this->getMockBuilder(Region::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                'getCode',
                'getDefaultName',
            ])
            ->getMock();

        $this->regionFactory = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->regionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->region);

        $this->regionData = $this->getMockBuilder(RegionInterface::class)
            ->getMockForAbstractClass();

        $this->regionDataFactory = $this->getMockBuilder(RegionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'create',
            ])
            ->getMock();
        $this->regionDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->regionData);
    }

    /**
     * Prepare form
     */
    protected function prepareForm(): void
    {
        $this->form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test form without formKey
     */
    public function testExecuteNoFormKey(): void
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * Test executing without post data
     */
    public function testExecuteNoPostData(): void
    {
        $postValue = 'post_value';
        $url = 'url';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(false);
        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postValue);

        $this->session->expects($this->once())
            ->method('setAddressFormData')
            ->with($postValue)
            ->willReturnSelf();

        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/edit', [])
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(UrlInterface::class)
            ->willReturn($urlBuilder);

        $this->redirect->expects($this->once())
            ->method('error')
            ->with($url)
            ->willReturn($url);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * Tests executing
     *
     * @param int $addressId
     * @param int $countryId
     * @param int $customerId
     * @param int $regionId
     * @param string $region
     * @param string $regionCode
     * @param int $newRegionId
     * @param string $newRegion
     * @param string $newRegionCode
     * @dataProvider dataProviderTestExecute
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testExecute(
        $addressId,
        $countryId,
        $customerId,
        $regionId,
        $region,
        $regionCode,
        $newRegionId,
        $newRegion,
        $newRegionCode,
        $existingDefaultBilling = false,
        $existingDefaultShipping = false,
        $setDefaultBilling = false,
        $setDefaultShipping = false
    ): void {
        $existingAddressData = [
            'country_id' => $countryId,
            'region_id' => $regionId,
            'region' => $region,
            'region_code' => $regionCode,
            'customer_id' => $customerId,
            'default_billing' => $existingDefaultBilling,
            'default_shipping' => $existingDefaultShipping,
        ];
        $newAddressData = [
            'country_id' => $countryId,
            'region_id' => $newRegionId,
            'region' => $newRegion,
            'region_code' => $newRegionCode,
            'customer_id' => $customerId
        ];

        $url = 'success_url';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap([
                ['id', null, $addressId],
                ['default_billing', $existingDefaultBilling, $setDefaultBilling],
                ['default_shipping', $existingDefaultShipping, $setDefaultShipping],
            ]);

        $this->addressRepository->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willReturn($this->addressData);
        $this->addressRepository->expects($this->once())
            ->method('save')
            ->with($this->addressData)
            ->willReturnSelf();

        $this->customerAddressMapper->expects($this->once())
            ->method('toFlatArray')
            ->with($this->addressData)
            ->willReturn($existingAddressData);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with('customer_address', 'customer_address_edit', $existingAddressData)
            ->willReturn($this->form);

        $this->form->expects($this->once())
            ->method('extractData')
            ->with($this->request)
            ->willReturn($newAddressData);
        $this->form->expects($this->once())
            ->method('compactData')
            ->with($newAddressData)
            ->willReturn($newAddressData);

        $this->region->expects($this->any())
            ->method('load')
            ->with($newRegionId)
            ->willReturn($this->region);
        $this->region->expects($this->any())
            ->method('getCode')
            ->willReturn($newRegionCode);
        $this->region->expects($this->any())
            ->method('getDefaultName')
            ->willReturn($newRegion);

        $regionData = [
            RegionInterface::REGION_ID => !empty($newRegionId) ? $newRegionId : null,
            RegionInterface::REGION => !empty($newRegion) ? $newRegion : null,
            RegionInterface::REGION_CODE => !empty($newRegionCode) ? $newRegionCode : null,
        ];

        $this->dataObjectHelper->expects($this->exactly(2))
            ->method('populateWithArray')
            ->willReturnMap([
                [
                    $this->regionData,
                    $regionData,
                    RegionInterface::class,
                    $this->dataObjectHelper,
                ],
                [
                    $this->addressData,
                    array_merge($existingAddressData, $newAddressData),
                    AddressInterface::class,
                    $this->dataObjectHelper,
                ],
            ]);

        $this->session->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->addressData->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->addressData->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturnSelf();
        $this->addressData->expects($this->once())
            ->method('setIsDefaultBilling')
            ->with($setDefaultBilling)
            ->willReturnSelf();
        $this->addressData->expects($this->once())
            ->method('setIsDefaultShipping')
            ->with($setDefaultShipping)
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the address.'))
            ->willReturnSelf();

        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/index', ['_secure' => true])
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(UrlInterface::class)
            ->willReturn($urlBuilder);

        $this->redirect->expects($this->once())
            ->method('success')
            ->with($url)
            ->willReturn($url);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * @return array
     */
    public function dataProviderTestExecute(): array
    {
        return [
            [1, 1, 1, null, '', null, '', null, ''],
            [1, 1, 1, '', null, '', null, '', null],

            [1, 1, 1, null, null, null, 12, null, null],
            [1, 1, 1, null, null, null, 1, 'California', null],
            [1, 1, 1, null, null, null, 1, 'California', 'CA'],

            [1, 1, 1, null, null, null, 1, null, 'CA'],
            [1, 1, 1, null, null, null, null, null, 'CA'],

            [1, 1, 1, 2, null, null, null, null, null],
            [1, 1, 1, 2, 'Alaska', null, null, null, null],
            [1, 1, 1, 2, 'Alaska', 'AK', null, null, null],

            [1, 1, 1, 2, null, null, null, null, null],
            [1, 1, 1, 2, 'Alaska', null, null, null, null],
            [1, 1, 1, 2, 'Alaska', 'AK', null, null, null],

            [1, 1, 1, 2, null, null, 12, null, null],
            [1, 1, 1, 2, 'Alaska', null, 12, null, 'CA'],
            [1, 1, 1, 2, 'Alaska', 'AK', 12, 'California', null, true, true, true, false],

            [1, 1, 1, 2, null, null, 12, null, null, false, false, true, false],
            [1, 1, 1, 2, 'Alaska', null, 12, null, 'CA', true, false, true, false],
            [1, 1, 1, 2, 'Alaska', 'AK', 12, 'California', null, true, true, true, true],
        ];
    }

    /**
     * Tests input exception
     */
    public function testExecuteInputException(): void
    {
        $addressId = 1;
        $postValue = 'post_value';
        $url = 'result_url';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->with('id')
            ->willReturn($addressId);
        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postValue);

        $this->addressRepository->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willThrowException(new InputException(__('InputException')));

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('InputException')
            ->willReturnSelf();

        $this->session->expects($this->once())
            ->method('setAddressFormData')
            ->with($postValue)
            ->willReturnSelf();

        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/edit', ['id' => $addressId])
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(UrlInterface::class)
            ->willReturn($urlBuilder);

        $this->redirect->expects($this->once())
            ->method('error')
            ->with($url)
            ->willReturn($url);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * Tests exception
     */
    public function testExecuteException(): void
    {
        $addressId = 1;
        $postValue = 'post_value';
        $url = 'result_url';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($addressId);
        $this->request->expects($this->never())
            ->method('getPostValue')
            ->willReturn($postValue);

        $exception = new \Exception('Exception');
        $this->addressRepository->expects($this->once())
            ->method('getById')
            ->with($addressId)
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, __('We can\'t save the address.'))
            ->willReturnSelf();

        $this->session->expects($this->never())
            ->method('setAddressFormData')
            ->with($postValue)
            ->willReturnSelf();

        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/index')
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(UrlInterface::class)
            ->willReturn($urlBuilder);

        $this->redirect->expects($this->once())
            ->method('error')
            ->with($url)
            ->willReturn($url);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }
}
