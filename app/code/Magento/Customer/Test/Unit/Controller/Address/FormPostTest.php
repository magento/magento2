<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Address\FormPost;
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
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormPostTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FormPost
     */
    protected $model;

    /**
     * @var Context |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var Session |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var FormKeyValidator |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    /**
     * @var FormFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactory;

    /**
     * @var AddressRepositoryInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepository;

    /**
     * @var AddressInterfaceFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressDataFactory;

    /**
     * @var RegionInterfaceFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionDataFactory;

    /**
     * @var DataObjectProcessor |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProcessor;

    /**
     * @var DataObjectHelper |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var ForwardFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactory;

    /**
     * @var PageFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    /**
     * @var RegionFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionFactory;

    /**
     * @var RequestInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ResultRedirect |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var RedirectFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var RedirectInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var ObjectManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var AddressInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressData;

    /**
     * @var RegionInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionData;

    /**
     * @var Form |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var HelperData |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperData;

    /**
     * @var Region |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $region;

    /**
     * @var ManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Model\Address\Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAddressMapper;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->prepareContext();

        $this->session = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
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

        $this->dataProcessor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultForwardFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\ForwardFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperData = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerAddressMapper = $this->getMockBuilder(\Magento\Customer\Model\Address\Mapper::class)
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods([
                'isPost',
                'getPostValue',
                'getParam',
            ])
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
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
        $this->addressRepository = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->addressData = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $this->addressDataFactory = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterfaceFactory::class)
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
        $this->region = $this->getMockBuilder(\Magento\Directory\Model\Region::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'load',
                'getCode',
                'getDefaultName',
            ])
            ->getMock();

        $this->regionFactory = $this->getMockBuilder(\Magento\Directory\Model\RegionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->regionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->region);

        $this->regionData = $this->getMockBuilder(\Magento\Customer\Api\Data\RegionInterface::class)
            ->getMockForAbstractClass();

        $this->regionDataFactory = $this->getMockBuilder(\Magento\Customer\Api\Data\RegionInterfaceFactory::class)
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
        $this->form = $this->getMockBuilder(\Magento\Customer\Model\Metadata\Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactory = $this->getMockBuilder(\Magento\Customer\Model\Metadata\FormFactory::class)
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

        $urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/edit', [])
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\UrlInterface::class)
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
        $newRegionCode
    ): void {
        $existingAddressData = [
            'country_id' => $countryId,
            'region_id' => $regionId,
            'region' => $region,
            'region_code' => $regionCode,
            'customer_id' => $customerId
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
                ['default_billing', false, $addressId],
                ['default_shipping', false, $addressId],
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
                    \Magento\Customer\Api\Data\RegionInterface::class,
                    $this->dataObjectHelper,
                ],
                [
                    $this->addressData,
                    array_merge($existingAddressData, $newAddressData),
                    \Magento\Customer\Api\Data\AddressInterface::class,
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
            ->with()
            ->willReturnSelf();
        $this->addressData->expects($this->once())
            ->method('setIsDefaultShipping')
            ->with()
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You saved the address.'))
            ->willReturnSelf();

        $urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/index', ['_secure' => true])
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\UrlInterface::class)
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
            [1, 1, 1, 2, 'Alaska', 'AK', 12, 'California', null],

            [1, 1, 1, 2, null, null, 12, null, null],
            [1, 1, 1, 2, 'Alaska', null, 12, null, 'CA'],
            [1, 1, 1, 2, 'Alaska', 'AK', 12, 'California', null],
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

        $urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/edit', ['id' => $addressId])
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\UrlInterface::class)
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

        $exception = new \Exception(__('Exception'));
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

        $urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();
        $urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/index')
            ->willReturn($url);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\UrlInterface::class)
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
