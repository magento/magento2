<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\AdminOrder;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\AdminOrder\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    const CUSTOMER_ID = 1;

    /** @var \Magento\Sales\Model\AdminOrder\Create */
    protected $adminOrderCreate;

    /** @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionQuoteMock;

    /** @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactoryMock;

    /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerFactoryMock;

    /** @var \Magento\Quote\Model\Quote\Item\Updater|\PHPUnit_Framework_MockObject_MockObject */
    protected $itemUpdater;

    /** @var \Magento\Customer\Model\Customer\Mapper|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMapper;

    /**
     * @var Product\Quote\Initializer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteInitializerMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactoryMock;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Sales\Model\AdminOrder\EmailSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailSenderMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagementMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $configMock = $this->createMock(\Magento\Sales\Model\Config::class);
        $this->sessionQuoteMock = $this->createMock(\Magento\Backend\Model\Session\Quote::class);
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $copyMock = $this->createMock(\Magento\Framework\DataObject\Copy::class);
        $messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->formFactoryMock = $this->createPartialMock(\Magento\Customer\Model\Metadata\FormFactory::class, ['create']);
        $this->customerFactoryMock = $this->createPartialMock(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class, ['create']);

        $this->itemUpdater = $this->createMock(\Magento\Quote\Model\Quote\Item\Updater::class);

        $this->objectFactory = $this->getMockBuilder(\Magento\Framework\DataObject\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->customerMapper = $this->getMockBuilder(
            \Magento\Customer\Model\Customer\Mapper::class
        )->setMethods(['toFlatArray'])->disableOriginalConstructor()->getMock();

        $this->quoteInitializerMock = $this->createMock(\Magento\Sales\Model\AdminOrder\Product\Quote\Initializer::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->addressRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AddressRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->addressFactoryMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        $this->groupRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\GroupRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->scopeConfigMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            '',
            false
        );
        $this->emailSenderMock = $this->createMock(\Magento\Sales\Model\AdminOrder\EmailSender::class);
        $this->accountManagementMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AccountManagementInterface::class,
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->adminOrderCreate = $objectManagerHelper->getObject(
            \Magento\Sales\Model\AdminOrder\Create::class,
            [
                'objectManager' => $objectManagerMock,
                'eventManager' => $eventManagerMock,
                'coreRegistry' => $registryMock,
                'salesConfig' => $configMock,
                'quoteSession' => $this->sessionQuoteMock,
                'logger' => $loggerMock,
                'objectCopyService' => $copyMock,
                'messageManager' => $messageManagerMock,
                'quoteInitializer' => $this->quoteInitializerMock,
                'customerRepository' => $this->customerRepositoryMock,
                'addressRepository' => $this->addressRepositoryMock,
                'addressFactory' => $this->addressFactoryMock,
                'metadataFormFactory' => $this->formFactoryMock,
                'customerFactory' => $this->customerFactoryMock,
                'groupRepository' => $this->groupRepositoryMock,
                'quoteItemUpdater' => $this->itemUpdater,
                'customerMapper' => $this->customerMapper,
                'objectFactory' => $this->objectFactory,
                'accountManagement' => $this->accountManagementMock,
                'dataObjectHelper' => $this->dataObjectHelper,
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

        foreach ($attributes as $attribute) {
            $attributeMock = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);

            $attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attribute[0]));

            $attributeMocks[] = $attributeMock;
        }

        $customerGroupMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getTaxClassId']
        );
        $customerGroupMock->expects($this->once())->method('getTaxClassId')->will($this->returnValue($taxClassId));
        $customerFormMock = $this->createMock(\Magento\Customer\Model\Metadata\Form::class);
        $customerFormMock->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnValue([$attributeMocks[1]]));
        $customerFormMock->expects($this->any())->method('extractData')->will($this->returnValue([]));
        $customerFormMock->expects($this->any())->method('restoreData')->will($this->returnValue(['group_id' => 1]));

        $customerFormMock->expects($this->any())
            ->method('prepareRequest')
            ->will($this->returnValue($this->createMock(\Magento\Framework\App\RequestInterface::class)));

        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerMapper->expects($this->atLeastOnce())
            ->method('toFlatArray')
            ->willReturn(['group_id' => 1]);

        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteMock->expects($this->any())->method('getCustomer')->will($this->returnValue($customerMock));
        $quoteMock->expects($this->once())
            ->method('addData')
            ->with(
            [
                'customer_group_id' => $attributes[1][1],
                'customer_tax_class_id' => $taxClassId
            ]
        );
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $customerMock,
                ['group_id' => 1], \Magento\Customer\Api\Data\CustomerInterface::class
            );

        $this->formFactoryMock->expects($this->any())->method('create')->will($this->returnValue($customerFormMock));
        $this->sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->customerFactoryMock->expects($this->any())->method('create')->will($this->returnValue($customerMock));

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->will($this->returnValue($customerGroupMock));

        $this->adminOrderCreate->setAccountData(['group_id' => 1]);
    }

    public function testUpdateQuoteItemsNotArray()
    {
        $object = $this->adminOrderCreate->updateQuoteItems('string');
        $this->assertEquals($this->adminOrderCreate, $object);
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

        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);

        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->will($this->returnValue($itemMock));

        $this->sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->itemUpdater->expects($this->once())
            ->method('update')
            ->with($this->equalTo($itemMock), $this->equalTo($items[1]))
            ->will($this->returnSelf());

        $this->adminOrderCreate->setRecollect(false);
        $object = $this->adminOrderCreate->updateQuoteItems($items);
        $this->assertEquals($this->adminOrderCreate, $object);
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

        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $itemMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue($qty));

        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteMock->expects($this->once())
            ->method('updateItem')
            ->will($this->returnValue($itemMock));

        $this->sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));

        $expectedInfo = $items[1];
        $expectedInfo['qty'] = $qty;
        $this->itemUpdater->expects($this->once())
            ->method('update')
            ->with($this->equalTo($itemMock), $this->equalTo($expectedInfo));

        $this->adminOrderCreate->setRecollect(false);
        $object = $this->adminOrderCreate->updateQuoteItems($items);
        $this->assertEquals($this->adminOrderCreate, $object);
    }

    public function testApplyCoupon()
    {
        $couponCode = '';
        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getShippingAddress', 'setCouponCode']);
        $this->sessionQuoteMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $addressMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, ['setCollectShippingRates', 'setFreeShipping']);
        $quoteMock->expects($this->exactly(2))->method('getShippingAddress')->willReturn($addressMock);
        $quoteMock->expects($this->once())->method('setCouponCode')->with($couponCode)->willReturnSelf();

        $addressMock->expects($this->once())->method('setCollectShippingRates')->with(true)->willReturnSelf();
        $addressMock->expects($this->once())->method('setFreeShipping')->with(null)->willReturnSelf();

        $object = $this->adminOrderCreate->applyCoupon($couponCode);
        $this->assertEquals($this->adminOrderCreate, $object);
    }
}
