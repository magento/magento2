<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\SalesRule;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\OfflineShipping\Model\SalesRule\Rule;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\ValidateCouponCode;
use Magento\SalesRule\Model\Validator\Pool;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CalculatorTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Calculator|MockObject
     */
    private Calculator $_model;

    /**
     * @var Utility
     */
    private Utility $validatorUtility;

    /**
     * @var Context|MockObject
     */
    private Context $context;

    /**
     * @var Registry|MockObject
     */
    private Registry $registry;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var Data|MockObject
     */
    private Data $catalogData;

    /**
     * @var RulesApplier|MockObject
     */
    private RulesApplier $rulesApplier;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var Pool|MockObject
     */
    private Pool $validators;

    /**
     * @var ManagerInterface|MockObject
     */
    private ManagerInterface $messageManager;

    /**
     * @var AbstractResource|MockObject
     */
    private AbstractResource $resource;

    /**
     * @var AbstractDb|MockObject
     */
    private AbstractDb $resourceCollection;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var CartFixedDiscount|MockObject
     */
    private CartFixedDiscount $cartFixedDiscount;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ValidateCouponCode|MockObject
     */
    private ValidateCouponCode $validateCouponCode;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->validatorUtility = $this->createMock(Utility::class);
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->catalogData = $this->createMock(Data::class);
        $this->rulesApplier = $this->createMock(RulesApplier::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->validators = $this->createMock(Pool::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->resource = $this->createMock(AbstractResource::class);
        $this->resourceCollection = $this->createMock(AbstractDb::class);
        $this->data = [];
        $this->cartFixedDiscount = $this->createMock(CartFixedDiscount::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->validateCouponCode = $this->createMock(ValidateCouponCode::class);

        $this->_model = new Calculator(
            $this->context,
            $this->registry,
            $this->collectionFactory,
            $this->catalogData,
            $this->validatorUtility,
            $this->rulesApplier,
            $this->priceCurrency,
            $this->validators,
            $this->messageManager,
            $this->resource,
            $this->resourceCollection,
            $this->data,
            $this->cartFixedDiscount,
            $this->storeManager,
            $this->validateCouponCode
        );
    }

    /**
     * @return bool
     * @throws \Zend_Db_Select_Exception
     */
    public function testProcessFreeShipping()
    {
        $addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->createPartialMock(Item::class, ['getAddress', '__wakeup']);
        $item->expects($this->once())->method('getAddress')->willReturn($addressMock);

        $ruleCollection = $this->createMock(Collection::class);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);
        $ruleCollection->method('setValidationFilter')->willReturnSelf();
        $ruleCollection->method('addFieldToFilter')->willReturnSelf();
        $ruleCollection->method('load')->willReturnSelf();
        $ruleCollection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $this->assertInstanceOf(
            Calculator::class,
            $this->_model->processFreeShipping($item)
        );

        return true;
    }

    /**
     * @return void
     * @throws Exception
     * @throws \Zend_Db_Select_Exception
     */
    public function testProcessFreeShippingWithChildren()
    {
        $addressMock = $this->createMock(Address::class);
        $childItem = $this->createMock(Item::class);
        $item = $this->createPartialMockWithReflection(
            Item::class,
            ['getAddress', '__wakeup', 'getHasChildren', 'isShipSeparately', 'setFreeShippingMethod', 'setFreeShipping']
        );

        $item->expects($this->once())->method('getHasChildren')->willReturn([$childItem]);
        $item->expects($this->exactly(2))->method('getAddress')->willReturn($addressMock);
        $item->expects($this->once())->method('isShipSeparately')->willReturn(true);
        $item->expects($this->exactly(2))->method('setFreeShipping');
        $item->expects($this->once())->method('setFreeShippingMethod');

        $actions = $this->createPartialMockWithReflection(\Magento\Rule\Model\Action\Collection::class, ['validate']);
        $actions->expects($this->once())->method('validate')->willReturn(true);
        $rule = $this->createPartialMockWithReflection(
            SalesRule::class,
            ['getActions', 'getSimpleFreeShipping']
        );
        $rule->expects($this->once())->method('getActions')->willReturn($actions);
        $rule->expects($this->once())->method('getSimpleFreeShipping')->willReturn(Rule::FREE_SHIPPING_ITEM);
        $ruleCollection = $this->createMock(Collection::class);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);
        $ruleCollection->method('setValidationFilter')->willReturnSelf();
        $ruleCollection->method('addFieldToFilter')->willReturnSelf();
        $ruleCollection->method('load')->willReturnSelf();
        $ruleCollection->method('getIterator')->willReturn(new \ArrayIterator([$rule]));

        $this->validatorUtility->expects($this->once())->method('canProcessRule')->willReturn(true);

        $this->_model->processFreeShipping($item);
    }
}
