<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerBuilderMock;

    /** @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerGroupServiceMock;

    protected function setUp()
    {
        $objectManagerMock = $this->getMock('Magento\ObjectManager');
        $eventManagerMock = $this->getMock('Magento\Event\ManagerInterface');
        $registryMock = $this->getMock('Magento\Registry');
        $configMock = $this->getMock('Magento\Sales\Model\Config', [], [], '', false);
        $this->sessionQuoteMock = $this->getMock('Magento\Backend\Model\Session\Quote', [], [], '', false);
        $loggerMock = $this->getMock('Magento\Logger', [], [], '', false);
        $copyMock = $this->getMock('Magento\Object\Copy', [], [], '', false);
        $messageManagerMock = $this->getMock('Magento\Message\ManagerInterface');
        $customerAccountServiceMock =
            $this->getMock('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customerAddressServiceMock =
            $this->getMock('Magento\Customer\Service\V1\CustomerAddressServiceInterface');
        $addressBuilderMock =
            $this->getMock('Magento\Customer\Service\V1\Data\AddressBuilder', [], [], '', false);
        $this->formFactoryMock = $this->getMock('Magento\Customer\Model\Metadata\FormFactory', [], [], '', false);
        $this->customerBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            [],
            [],
            '',
            false
        );
        $customerHelperMock = $this->getMock('Magento\Customer\Helper\Data', [], [], '', false);
        $this->customerGroupServiceMock = $this->getMock(
            'Magento\Customer\Service\V1\CustomerGroupServiceInterface'
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
                'customerAccountService' => $customerAccountServiceMock,
                'customerAddressService' => $customerAddressServiceMock,
                'customerAddressBuilder' => $addressBuilderMock,
                'metadataFormFactory' => $this->formFactoryMock,
                'customerBuilder' => $this->customerBuilderMock,
                'customerHelper' => $customerHelperMock,
                'customerGroupService' => $this->customerGroupServiceMock
            ]
        );
    }

    public function testSetAccountData()
    {
        $taxClassId = 1;
        $attributes = [['email', 'user@example.com'], ['group_id', 1]];
        $attributeMocks = [];

        foreach ($attributes as $attribute) {
            $attributeMock = $this->getMock(
                'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata',
                [], [], '', false);

            $attributeMock
                ->expects($this->any())
                ->method('getAttributeCode')
                ->will($this->returnValue($attribute[0]));

            $attributeMocks[] = $attributeMock;
        }

        $customerGroupMock = $this->getMock('Magento\Customer\Service\V1\Data\CustomerGroup', [], [], '', false);
        $customerGroupMock->expects($this->once())->method('getTaxClassId')->will($this->returnValue($taxClassId));
        $customerFormMock = $this->getMock('Magento\Customer\Model\Metadata\Form', [], [], '', false);
        $customerFormMock->expects($this->any())->method('getAttributes')->will($this->returnValue($attributeMocks));
        $customerFormMock->expects($this->any())->method('extractData')->will($this->returnValue([]));
        $customerFormMock->expects($this->any())->method('restoreData')->will($this->returnValue([]));

        $customerFormMock
            ->expects($this->any())
            ->method('prepareRequest')
            ->will($this->returnValue($this->getMock('Magento\App\RequestInterface')));

        $customerMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $customerMock->expects($this->any())->method('__toArray')
            ->will($this->returnValue(['email' => 'user@example.com', 'group_id' => 1]));
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $quoteMock->expects($this->any())->method('getCustomerData')->will($this->returnValue($customerMock));

        $quoteMock
            ->expects($this->once())
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
        $this->customerBuilderMock->expects($this->any())->method('populateWithArray')->will($this->returnSelf());
        $this->customerBuilderMock->expects($this->any())->method('create')->will($this->returnValue($customerMock));
        $this->customerBuilderMock->expects($this->any())->method('mergeDataObjectWithArray')
            ->will($this->returnArgument(0));

        $this->customerGroupServiceMock
            ->expects($this->once())
            ->method('getGroup')
            ->will($this->returnValue($customerGroupMock));

        $this->adminOrderCreate->setAccountData([]);
    }
}
