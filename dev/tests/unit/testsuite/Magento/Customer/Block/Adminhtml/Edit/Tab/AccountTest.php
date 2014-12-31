<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\AttributeValue;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactoryMock;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encoderInterfaceMock;

    /** @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerFormFactoryMock;

    /** @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeMock;

    /** @var \Magento\Customer\Model\Options|\PHPUnit_Framework_MockObject_MockObject */
    protected $options;

    /** @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $accountManagementMock;

    /** @var \Magento\Customer\Api\CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadataMock;

    /** @var \Magento\Customer\Api\Data\CustomerDataBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerBuilderMock;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensibleDataObjectConverterMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->contextMock = $this->getMockBuilder('Magento\Backend\Block\Template\Context')
            ->setMethods(
                ['getBackendSession', 'getLayout', 'getStoreManager', 'getUrlBuilder']
            )->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMock('Magento\Framework\Registry');
        $this->formFactoryMock = $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false);
        $this->encoderInterfaceMock = $this->getMock('Magento\Framework\Json\EncoderInterface');
        $this->customerFormFactoryMock = $this->getMockBuilder('Magento\Customer\Model\Metadata\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMock('Magento\Store\Model\System\Store', [], [], '', false);
        $this->options = $this->getMockBuilder('Magento\Customer\Model\Options')
            ->setMethods(['getNamePrefixOptions', 'getNameSuffixOptions'])
            ->disableOriginalConstructor()->getMock();
        $this->accountManagementMock = $this->getMock(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $this->customerMetadataMock = $this->getMock(
            'Magento\Customer\Api\CustomerMetadataInterface'
        );
        $this->customerBuilderMock = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerDataBuilder')
            ->setMethods(['populateWithArray', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensibleDataObjectConverterMock = $this->getMockBuilder(
            'Magento\Framework\Api\ExtensibleDataObjectConverter'
        )->setMethods(['toFlatArray'])->disableOriginalConstructor()->getMock();
    }

    /**
     * @param $customerData
     * @param $isSingleStoreMode
     * @param $canModifyCustomer
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function _setupStoreMode($customerData, $isSingleStoreMode, $canModifyCustomer)
    {
        $backendSessionMock = $this->getMock('\Magento\Backend\Model\Session', ['getCustomerData'], [], '', false);
        $backendSessionMock->expects($this->any())->method('getCustomerData')->will($this->returnValue([]));

        $layoutMock = $this->getMock('\Magento\Framework\View\Layout\Element\Layout', ['createBlock'], [], '', false);
        $layoutMock->expects($this->at(0))->method('createBlock')
            ->with('Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group')
            ->will($this->returnValue(
                $this->objectManagerHelper->getObject('Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group')
            ));
        $layoutMock->expects($this->at(1))->method('createBlock')
            ->with('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element')
            ->will($this->returnValue(
                $this->objectManagerHelper->getObject(
                    'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
                )
            ));
        if (empty($customerData['id'])) {
            $layoutMock->expects($this->at(2))->method('createBlock')
                ->with('Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Sendemail')
                ->will($this->returnValue($this->objectManagerHelper->getObject(
                    'Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Sendemail'
                ))
            );
        }

        $urlBuilderMock = $this->getMock('\Magento\Backend\Model\Url', ['getBaseUrl'], [], '', false);
        $urlBuilderMock->expects($this->once())->method('getBaseUrl')->will($this->returnValue('someUrl'));

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock->expects($this->any())->method('isSingleStoreMode')
            ->will($this->returnValue($isSingleStoreMode));

        $customerObject = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface');
        if (!empty($customerData['id'])) {
            $customerObject->expects($this->any())->method('getId')->will($this->returnValue($customerData['id']));
        }

        $fieldset = $this->getMockBuilder('\Magento\Framework\Data\Form\Element\Fieldset')
            ->setMethods(['getForm', 'addField', 'removeField'])
            ->disableOriginalConstructor()
            ->getMock();
        $accountForm = $this->getMockBuilder('Magento\Framework\Data\Form')
            ->setMethods(['create', 'addFieldset', 'getElement', 'setValues'])
            ->disableOriginalConstructor()
            ->getMock();

        $accountForm->expects($this->any())->method('addFieldset')->with('base_fieldset')
            ->will($this->returnValue($fieldset));
        $accountForm->expects($this->any())->method('setValues')->will($this->returnValue($accountForm));
        $fieldset->expects($this->any())->method('getForm')->will($this->returnValue($accountForm));
        $formElement = $this->getMockBuilder('\Magento\Framework\Data\Form\Element\Select')
            ->setMethods(['setRenderer', 'addClass', 'setDisabled'])
            ->disableOriginalConstructor()->getMock();

        $formElement->expects($this->any())->method('setRenderer')->will($this->returnValue(null));
        $formElement->expects($this->any())->method('addClass')->will($this->returnValue(null));
        $formElement->expects($this->any())->method('setDisabled')->will($this->returnValue(null));
        $accountForm->expects($this->any())->method('getElement')->withAnyParameters()
            ->will($this->returnValue($formElement));

        $fieldset->expects($this->any())->method('addField')->will($this->returnValue($formElement));

        $customerForm = $this->getMock('\Magento\Customer\Model\Metadata\Form', ['getAttributes'], [], '', false);
        $customerForm->expects($this->any())->method('getAttributes')->will($this->returnValue([]));

        $this->contextMock->expects($this->any())->method('getBackendSession')
            ->will($this->returnValue($backendSessionMock));
        $this->contextMock->expects($this->any())->method('getLayout')
            ->will($this->returnValue($layoutMock));
        $this->contextMock->expects($this->any())->method('getUrlBuilder')
            ->will($this->returnValue($urlBuilderMock));
        $this->contextMock->expects($this->any())->method('getStoreManager')
            ->will($this->returnValue($storeManagerMock));
        $this->customerBuilderMock->expects($this->any())->method('populateWithArray')
            ->will($this->returnValue($this->customerBuilderMock));
        $this->customerBuilderMock->expects($this->any())->method('create')
            ->will($this->returnValue($customerObject));
        $this->options->expects($this->any())->method('getNamePrefixOptions')
            ->will($this->returnValue(['Pref1', 'Pref2']));
        $this->options->expects($this->any())->method('getNameSuffixOptions')
            ->will($this->returnValue(['Suf1', 'Suf2']));
        $this->formFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($accountForm));
        $this->extensibleDataObjectConverterMock->expects($this->any())->method('toFlatArray')
            ->will($this->returnValue($customerData));
        $this->customerFormFactoryMock
            ->expects($this->any())
            ->method('create')
            ->with(
                'customer',
                'adminhtml_customer',
                $this->extensibleDataObjectConverterMock->toFlatArray(
                    $customerObject,
                    '\Magento\Customer\Api\Data\CustomerInterface'
                )
            )
            ->will($this->returnValue($customerForm));
        $this->accountManagementMock->expects($this->any())->method('isReadOnly')->withAnyParameters()
            ->will($this->returnValue(!$canModifyCustomer));
        $this->accountManagementMock->expects($this->any())->method('getConfirmationStatus')
            ->withAnyParameters()
            ->will($this->returnValue(AccountManagementInterface::ACCOUNT_CONFIRMED));
    }

    /**
     * @dataProvider getInitFormData
     */
    public function testInitForm($customerData, $isSingleStoreMode, $canModifyCustomer)
    {
        $this->_setupStoreMode($customerData, $isSingleStoreMode, $canModifyCustomer);
        $this->objectManagerHelper->getObject(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\Account',
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
                'jsonEncoder' => $this->encoderInterfaceMock,
                'customerFormFactory' => $this->customerFormFactoryMock,
                'systemStore' => $this->storeMock,
                'options' => $this->options,
                'accountManagement' => $this->accountManagementMock,
                'customerMetadata' => $this->customerMetadataMock,
                'customerBuilder' => $this->customerBuilderMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock
            ]
        )->initForm();
    }

    /**
     * Data provider for method testInitForm
     * @return array
     */
    public function getInitFormData()
    {
        return [
            [[], true, true],
            [['id' => 1], true, true],
            [[], false, false],
            [
                [
                    'id' => 1,
                    AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY => [
                        [AttributeValue::ATTRIBUTE_CODE => 'test_attribute1', AttributeValue::VALUE => 'test_value1'],
                        [AttributeValue::ATTRIBUTE_CODE => 'test_attribute2', AttributeValue::VALUE => 'test_value2'],
                    ],
                ],
                false,
                false
            ],
        ];
    }
}
