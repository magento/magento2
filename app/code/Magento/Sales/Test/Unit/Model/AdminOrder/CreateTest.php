<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\AdminOrder;

use Magento\Sales\Model\AdminOrder\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CreateTest extends \PHPUnit_Framework_TestCase
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
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $eventManagerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class);
        $registryMock = $this->getMock(\Magento\Framework\Registry::class);
        $configMock = $this->getMock(\Magento\Sales\Model\Config::class, [], [], '', false);
        $this->sessionQuoteMock = $this->getMock(\Magento\Backend\Model\Session\Quote::class, [], [], '', false);
        $loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $copyMock = $this->getMock(\Magento\Framework\DataObject\Copy::class, [], [], '', false);
        $messageManagerMock = $this->getMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->formFactoryMock = $this->getMock(
            \Magento\Customer\Model\Metadata\FormFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->itemUpdater = $this->getMock(\Magento\Quote\Model\Quote\Item\Updater::class, [], [], '', false);

        $this->objectFactory = $this->getMockBuilder(\Magento\Framework\DataObject\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->customerMapper = $this->getMockBuilder(
            \Magento\Customer\Model\Customer\Mapper::class
        )->setMethods(['toFlatArray'])->disableOriginalConstructor()->getMock();

        $this->quoteInitializerMock = $this->getMock(
            \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer::class,
            [],
            [],
            '',
            false
        );
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
        $this->addressFactoryMock = $this->getMock(
            \Magento\Customer\Api\Data\AddressInterfaceFactory::class,
            [],
            [],
            '',
            false
        );
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
        $this->emailSenderMock = $this->getMock(
            \Magento\Sales\Model\AdminOrder\EmailSender::class,
            [],
            [],
            '',
            false
        );
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
            $attributeMock = $this->getMock(
                \Magento\Customer\Api\Data\AttributeMetadataInterface::class,
                [],
                [],
                '',
                false
            );

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
        $customerFormMock = $this->getMock(\Magento\Customer\Model\Metadata\Form::class, [], [], '', false);
        $customerFormMock->expects($this->any())
            ->method('getAttributes')
            ->will($this->returnValue([$attributeMocks[1]]));
        $customerFormMock->expects($this->any())->method('extractData')->will($this->returnValue([]));
        $customerFormMock->expects($this->any())->method('restoreData')->will($this->returnValue(['group_id' => 1]));

        $customerFormMock->expects($this->any())
            ->method('prepareRequest')
            ->will($this->returnValue($this->getMock(\Magento\Framework\App\RequestInterface::class)));

        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false);
        $this->customerMapper->expects($this->atLeastOnce())
            ->method('toFlatArray')
            ->willReturn(['group_id' => 1]);


        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
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
        $this->adminOrderCreate->updateQuoteItems('string');
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

        $itemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->will($this->returnValue($itemMock));

        $this->sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->itemUpdater->expects($this->once())
            ->method('update')
            ->with($this->equalTo($itemMock), $this->equalTo($items[1]))
            ->will($this->returnSelf());

        $this->adminOrderCreate->setRecollect(false);
        $this->adminOrderCreate->updateQuoteItems($items);
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

        $itemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $itemMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue($qty));

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
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
        $this->adminOrderCreate->updateQuoteItems($items);
    }

    public function testApplyCoupon()
    {
        $couponCode = '';
        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getShippingAddress', 'setCouponCode'],
            [],
            '',
            false
        );
        $this->sessionQuoteMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);

        $addressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['setCollectShippingRates', 'setFreeShipping'],
            [],
            '',
            false
        );
        $quoteMock->expects($this->exactly(2))->method('getShippingAddress')->willReturn($addressMock);
        $quoteMock->expects($this->once())->method('setCouponCode')->with($couponCode)->willReturnSelf();

        $addressMock->expects($this->once())->method('setCollectShippingRates')->with(true)->willReturnSelf();
        $addressMock->expects($this->once())->method('setFreeShipping')->with(null)->willReturnSelf();

        $this->adminOrderCreate->applyCoupon($couponCode);
    }
}
