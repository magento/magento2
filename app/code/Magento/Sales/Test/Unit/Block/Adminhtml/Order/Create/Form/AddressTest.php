<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Create\Form;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Address;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends TestCase
{
    /**
     * @var QuoteSession|MockObject
     */
    private $quoteSession;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var DirectoryHelper|MockObject
     */
    private $directoryHelper;

    /**
     * @var int
     */
    private $defaultCountryId;

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var int
     */
    private $addressId;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactory;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $criteriaBuilder;

    /**
     * @var AddressInterface|MockObject
     */
    private $addressItem;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressService;

    /**
     * @var Mapper|MockObject
     */
    private $addressMapper;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->defaultCountryId = 1;
        $this->customerId = 10;
        $this->addressId = 100;

        $this->quoteSession = $this->getMockBuilder(QuoteSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->addMethods(['getCustomerId'])
            ->getMock();
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteSession->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->quoteSession->expects($this->any())
            ->method('getCustomerId')
            ->willReturn($this->customerId);
        $this->directoryHelper = $this->getMockBuilder(DirectoryHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDefaultCountry'])
            ->getMock();
        $this->directoryHelper->expects($this->any())
            ->method('getDefaultCountry')
            ->willReturn($this->defaultCountryId);
        $this->formFactory = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setField', 'setValue', 'setConditionType', 'create'])
            ->getMock();
        $this->criteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create', 'addFilters'])
            ->getMock();
        $this->addressService = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();
        $this->addressItem = $this->getMockBuilder(AddressInterface::class)
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->addressItem->expects($this->any())
            ->method('getId')
            ->willReturn($this->addressId);
        $this->addressMapper = $this->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toFlatArray'])
            ->getMock();

        $this->address = $this->objectManager->getObject(
            Address::class,
            [
                'directoryHelper' => $this->directoryHelper,
                'sessionQuote' => $this->quoteSession,
                'customerFormFactory' => $this->formFactory,
                'filterBuilder' => $this->filterBuilder,
                'criteriaBuilder' => $this->criteriaBuilder,
                'addressService' => $this->addressService,
                'addressMapper' => $this->addressMapper
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetAddressCollectionJson(): void
    {
        /** @var Form|MockObject $emptyForm */
        $emptyForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['outputData'])
            ->getMock();
        $emptyForm->expects($this->once())
            ->method('outputData')
            ->with(AttributeDataFactory::OUTPUT_FORMAT_JSON)
            ->willReturn('emptyFormData');

        /** @var Filter|MockObject $filter */
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder->expects($this->once())
            ->method('setField')
            ->with('parent_id')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())
            ->method('setValue')
            ->with($this->customerId)
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())
            ->method('create')
            ->willReturn($filter);

        /** @var SearchCriteria|MockObject $searchCriteria */
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->criteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->criteriaBuilder->expects($this->once())
            ->method('addFilters')
            ->with([$filter]);

        /** @var AddressSearchResultsInterface|MockObject $result */
        $result = $this->getMockBuilder(AddressSearchResultsInterface::class)
            ->addMethods(['getList'])
            ->getMockForAbstractClass();
        $result->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->addressItem]);
        $this->addressService->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($result);

        /** @var Form|MockObject $emptyForm */
        $addressForm = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['outputData'])
            ->getMock();
        $addressForm->expects($this->once())
            ->method('outputData')
            ->with(AttributeDataFactory::OUTPUT_FORMAT_JSON)
            ->willReturn('addressFormData');
        $this->addressMapper->expects($this->once())
            ->method('toFlatArray')
            ->with($this->addressItem)
            ->willReturn([]);

        $this->directoryHelper->expects($this->once())
            ->method('getDefaultCountry')
            ->with($this->store)
            ->willReturn($this->defaultCountryId);
        $this->formFactory
            ->method('create')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3 = [], $arg4 = false, $arg5 = false) use ($emptyForm, $addressForm) {
                    if ($arg1 === 'customer_address' && $arg2 === 'adminhtml_customer_address' &&
                        $arg3 === [AddressInterface::COUNTRY_ID => $this->defaultCountryId]) {
                        return $emptyForm;
                    } elseif ($arg1 === 'customer_address' && $arg2 === 'adminhtml_customer_address' &&
                        empty($arg3) && $arg4 === false && $arg5 === false) {
                        return $addressForm;
                    }
                }
            );

        $this->address->getAddressCollectionJson();
    }
}
