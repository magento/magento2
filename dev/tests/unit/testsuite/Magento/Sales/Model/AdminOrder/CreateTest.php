<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\AdminOrder;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    /** @var \Magento\Customer\Api\Data\CustomerDataBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerBuilderMock;

    /** @var \Magento\Sales\Model\Quote\Item\Updater|\PHPUnit_Framework_MockObject_MockObject */
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
     * @var \Magento\Customer\Api\Data\AddressDataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressBuilderMock;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactory;

    protected function setUp()
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $registryMock = $this->getMock('Magento\Framework\Registry');
        $configMock = $this->getMock('Magento\Sales\Model\Config', [], [], '', false);
        $this->sessionQuoteMock = $this->getMock('Magento\Backend\Model\Session\Quote', [], [], '', false);
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $copyMock = $this->getMock('Magento\Framework\Object\Copy', [], [], '', false);
        $messageManagerMock = $this->getMock('Magento\Framework\Message\ManagerInterface');
        $this->formFactoryMock = $this->getMock(
            'Magento\Customer\Model\Metadata\FormFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\CustomerDataBuilder',
            ['mergeDataObjectWithArray', 'populateWithArray', 'create'],
            [],
            '',
            false
        );

        $this->itemUpdater = $this->getMock('Magento\Sales\Model\Quote\Item\Updater', [], [], '', false);

        $this->objectFactory = $this->getMockBuilder('\Magento\Framework\Object\Factory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->customerMapper = $this->getMockBuilder(
            'Magento\Customer\Model\Customer\Mapper'
        )->setMethods(['toFlatArray'])->disableOriginalConstructor()->getMock();

        $this->quoteInitializerMock = $this->getMock(
            'Magento\Sales\Model\AdminOrder\Product\Quote\Initializer',
            [],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\CustomerRepositoryInterface',
            [],
            '',
            false
        );
        $this->addressRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AddressRepositoryInterface',
            [],
            '',
            false
        );
        $this->addressBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\AddressDataBuilder',
            [],
            [],
            '',
            false
        );
        $this->groupRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\GroupRepositoryInterface',
            [],
            '',
            false
        );
        $this->scopeConfigMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            '',
            false
        );
        $this->emailSenderMock = $this->getMock(
            'Magento\Sales\Model\AdminOrder\EmailSender',
            [],
            [],
            '',
            false
        );
        $this->accountManagementMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AccountManagementInterface',
            [],
            '',
            false
        );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->adminOrderCreate = $objectManagerHelper->getObject(
            'Magento\Sales\Model\AdminOrder\Create',
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
                'addressBuilder' => $this->addressBuilderMock,
                'metadataFormFactory' => $this->formFactoryMock,
                'customerBuilder' => $this->customerBuilderMock,
                'groupRepository' => $this->groupRepositoryMock,
                'quoteItemUpdater' => $this->itemUpdater,
                'customerMapper' => $this->customerMapper,
                'objectFactory' => $this->objectFactory,
                'accountManagement' => $this->accountManagementMock,
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
                'Magento\Customer\Api\Data\AttributeMetadataInterface',
                [],
                [],
                '',
                false
            );

            $attributeMock->expects($this->any())->method('getAttributeCode')->will($this->returnValue($attribute[0]));

            $attributeMocks[] = $attributeMock;
        }

        $customerGroupMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\GroupInterface',
            [],
            '',
            false,
            true,
            true,
            ['getTaxClassId']
        );
        $customerGroupMock->expects($this->once())->method('getTaxClassId')->will($this->returnValue($taxClassId));
        $customerFormMock = $this->getMock('Magento\Customer\Model\Metadata\Form', [], [], '', false);
        $customerFormMock->expects($this->any())->method('getAttributes')->will($this->returnValue($attributeMocks));
        $customerFormMock->expects($this->any())->method('extractData')->will($this->returnValue([]));
        $customerFormMock->expects($this->any())->method('restoreData')->will($this->returnValue([]));

        $customerFormMock->expects($this->any())
            ->method('prepareRequest')
            ->will($this->returnValue($this->getMock('Magento\Framework\App\RequestInterface')));

        $customerMock = $this->getMock('Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $this->customerMapper->expects($this->any())->method('toFlatArray')
            ->will($this->returnValue(['email' => 'user@example.com', 'group_id' => 1]));
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->any())->method('getCustomer')->will($this->returnValue($customerMock));
        $quoteMock->expects($this->once())
            ->method('addData')
            ->with(
            [
                'customer_email' => $attributes[0][1],
                'customer_group_id' => $attributes[1][1],
                'customer_tax_class_id' => $taxClassId
            ]
        );

        $this->formFactoryMock->expects($this->any())->method('create')->will($this->returnValue($customerFormMock));
        $this->sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->customerBuilderMock->expects($this->any())
            ->method('mergeDataObjectWithArray')
            ->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->any())->method('create')->will($this->returnValue($customerMock));

        $this->groupRepositoryMock->expects($this->once())
            ->method('getById')
            ->will($this->returnValue($customerGroupMock));

        $this->adminOrderCreate->setAccountData([]);
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

        $itemMock = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);

        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
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

        $itemMock = $this->getMock('Magento\Sales\Model\Quote\Item', [], [], '', false);
        $itemMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue($qty));

        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
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
}
