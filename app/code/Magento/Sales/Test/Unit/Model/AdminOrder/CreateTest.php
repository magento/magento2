<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\AdminOrder;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Updater;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\AdminOrder\Product;
use Magento\Quote\Model\QuoteFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    const CUSTOMER_ID = 1;

    /**
     * @var Create
     */
    private $adminOrderCreate;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteFactory;

    /**
     * @var SessionQuote|MockObject
     */
    private $sessionQuote;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactory;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    private $customerFactory;

    /**
     * @var Updater|MockObject
     */
    private $itemUpdater;

    /**
     * @var Mapper|MockObject
     */
    private $customerMapper;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepository;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    protected function setUp()
    {
        $this->formFactory = $this->createPartialMock(FormFactory::class, ['create']);
        $this->quoteFactory = $this->createPartialMock(QuoteFactory::class, ['create']);
        $this->customerFactory = $this->createPartialMock(CustomerInterfaceFactory::class, ['create']);

        $this->itemUpdater = $this->createMock(Updater::class);

        $this->quoteRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getForCustomer'])
            ->getMockForAbstractClass();

        $this->sessionQuote = $this->getMockBuilder(\Magento\Backend\Model\Session\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getStoreId', 'getCustomerId'])
            ->getMock();

        $this->customerMapper = $this->getMockBuilder(Mapper::class)
            ->setMethods(['toFlatArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupRepository = $this->getMockForAbstractClass(GroupRepositoryInterface::class);
        $this->dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->adminOrderCreate = $objectManagerHelper->getObject(
            Create::class,
            [
                'quoteSession' => $this->sessionQuote,
                'metadataFormFactory' => $this->formFactory,
                'customerFactory' => $this->customerFactory,
                'groupRepository' => $this->groupRepository,
                'quoteItemUpdater' => $this->itemUpdater,
                'customerMapper' => $this->customerMapper,
                'dataObjectHelper' => $this->dataObjectHelper,
                'quoteRepository' => $this->quoteRepository,
                'quoteFactory' => $this->quoteFactory,
            ]
        );
    }

    public function testSetAccountData()
    {
        $taxClassId = 1;
        $attributes = [
            ['email', 'user@example.com'],
            ['group_id', 1]
        ];
        $attributeMocks = [];

        foreach ($attributes as $value) {
            $attribute = $this->createMock(AttributeMetadataInterface::class);
            $attribute->method('getAttributeCode')
                ->willReturn($value[0]);

            $attributeMocks[] = $attribute;
        }

        $customerGroup = $this->getMockForAbstractClass(GroupInterface::class);
        $customerGroup->method('getTaxClassId')
            ->willReturn($taxClassId);
        $customerForm = $this->createMock(Form::class);
        $customerForm->method('getAttributes')
            ->willReturn([$attributeMocks[1]]);
        $customerForm
            ->method('extractData')
            ->willReturn([]);
        $customerForm
            ->method('restoreData')
            ->willReturn(['group_id' => 1]);

        $customerForm->method('prepareRequest')
            ->willReturn($this->createMock(RequestInterface::class));

        $customer = $this->createMock(CustomerInterface::class);
        $this->customerMapper->expects(self::atLeastOnce())
            ->method('toFlatArray')
            ->willReturn(['group_id' => 1]);

        $quote = $this->createMock(Quote::class);
        $quote->method('getCustomer')->willReturn($customer);
        $quote->method('addData')->with(
            [
                'customer_group_id' => $attributes[1][1],
                'customer_tax_class_id' => $taxClassId
            ]
        );
        $this->dataObjectHelper->method('populateWithArray')
            ->with(
                $customer,
                ['group_id' => 1],
                CustomerInterface::class
            );

        $this->formFactory->method('create')
            ->willReturn($customerForm);
        $this->sessionQuote
            ->method('getQuote')
            ->willReturn($quote);
        $this->customerFactory->method('create')
            ->willReturn($customer);

        $this->groupRepository->method('getById')
            ->willReturn($customerGroup);

        $this->adminOrderCreate->setAccountData(['group_id' => 1]);
    }

    public function testUpdateQuoteItemsNotArray()
    {
        $object = $this->adminOrderCreate->updateQuoteItems('string');
        self::assertEquals($this->adminOrderCreate, $object);
    }

    public function testUpdateQuoteItemsEmptyConfiguredOption()
    {
        $items = [
            1 => [
                'qty' => 10,
                'configured' => false,
                'action' => false
            ]
        ];

        $item = $this->createMock(Item::class);

        $quote = $this->createMock(Quote::class);
        $quote->method('getItemById')
            ->willReturn($item);

        $this->sessionQuote->method('getQuote')
            ->willReturn($quote);
        $this->itemUpdater->method('update')
            ->with(self::equalTo($item), self::equalTo($items[1]))
            ->willReturnSelf();

        $this->adminOrderCreate->setRecollect(false);
        $object = $this->adminOrderCreate->updateQuoteItems($items);
        self::assertEquals($this->adminOrderCreate, $object);
    }

    public function testUpdateQuoteItemsWithConfiguredOption()
    {
        $qty = 100000000;
        $items = [
            1 => [
                'qty' => 10,
                'configured' => true,
                'action' => false
            ]
        ];

        $item = $this->createMock(Item::class);
        $item->method('getQty')
            ->willReturn($qty);

        $quote = $this->createMock(Quote::class);
        $quote->method('updateItem')
            ->willReturn($item);

        $this->sessionQuote
            ->method('getQuote')
            ->willReturn($quote);

        $expectedInfo = $items[1];
        $expectedInfo['qty'] = $qty;
        $this->itemUpdater->method('update')
            ->with(self::equalTo($item), self::equalTo($expectedInfo));

        $this->adminOrderCreate->setRecollect(false);
        $object = $this->adminOrderCreate->updateQuoteItems($items);
        self::assertEquals($this->adminOrderCreate, $object);
    }

    public function testApplyCoupon()
    {
        $couponCode = '123';
        $quote = $this->createPartialMock(Quote::class, ['getShippingAddress', 'setCouponCode']);
        $this->sessionQuote->method('getQuote')
            ->willReturn($quote);

        $address = $this->createPartialMock(Address::class, ['setCollectShippingRates', 'setFreeShipping']);
        $quote->method('getShippingAddress')
            ->willReturn($address);
        $quote->method('setCouponCode')
            ->with($couponCode)
            ->willReturnSelf();

        $address->method('setCollectShippingRates')
            ->with(true)
            ->willReturnSelf();
        $address->method('setFreeShipping')
            ->with(0)
            ->willReturnSelf();

        $object = $this->adminOrderCreate->applyCoupon($couponCode);
        self::assertEquals($this->adminOrderCreate, $object);
    }

    public function testGetCustomerCart()
    {
        $storeId = 2;
        $customerId = 2;
        $cartResult = [
            'cart' => true,
        ];

        $this->quoteFactory->expects($this->once())
            ->method('create');
        $this->sessionQuote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->sessionQuote->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->quoteRepository->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId, [$storeId])
            ->willReturn($cartResult);

        $this->assertEquals($cartResult, $this->adminOrderCreate->getCustomerCart());
    }
}
