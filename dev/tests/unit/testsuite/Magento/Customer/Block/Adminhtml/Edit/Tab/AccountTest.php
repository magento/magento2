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

namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\Customer\Service\V1\CustomerAccountServiceInterface;

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

    /** @var \Magento\Customer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerHelperMock;

    /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerAccountServiceInterfaceMock;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadataServiceInterfaceMock;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerBuilderMock;

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
        $this->customerHelperMock = $this->getMockBuilder('Magento\Customer\Helper\Data')
            ->setMethods(['getNamePrefixOptions', 'getNameSuffixOptions'])
            ->disableOriginalConstructor()->getMock();
        $this->customerAccountServiceInterfaceMock = $this->getMock(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $this->customerMetadataServiceInterfaceMock = $this->getMock(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface'
        );
        $this->customerBuilderMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\CustomerBuilder')
            ->setMethods(['populateWithArray', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $customerData
     * @param $isSingleStoreMode
     * @param $canModifyCustomer
     */
    private function _setupStoreMode($customerData, $isSingleStoreMode, $canModifyCustomer)
    {
        $backendSessionMock = $this->getMock('\Magento\Backend\Model\Session', ['getCustomerData'], [], '', false);
        $backendSessionMock->expects($this->any())->method('getCustomerData')->will($this->returnValue([]));

        $layoutMock = $this->getMock('\Magento\Framework\View\Layout\Element\Layout', ['createBlock'], [], '', false);
        $layoutMock->expects($this->at(0))->method('createBlock')
            ->with('Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group')
            ->will($this->returnValue(
                $this->objectManagerHelper->getObject('\Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group')
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

        $customerObject = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Customer')
            ->setMethods(['__toArray', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerObject->expects($this->any())->method('__toArray')->will($this->returnValue($customerData));
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
        $this->customerHelperMock->expects($this->any())->method('getNamePrefixOptions')
            ->will($this->returnValue(['Pref1', 'Pref2']));
        $this->customerHelperMock->expects($this->any())->method('getNameSuffixOptions')
            ->will($this->returnValue(['Suf1', 'Suf2']));
        $this->formFactoryMock->expects($this->any())->method('create')
            ->will($this->returnValue($accountForm));
        $this->customerFormFactoryMock->expects($this->any())->method('create')->with('customer', 'adminhtml_customer')
            ->will($this->returnValue($customerForm));
        $this->customerAccountServiceInterfaceMock->expects($this->any())->method('canModify')->withAnyParameters()
            ->will($this->returnValue($canModifyCustomer));
        $this->customerAccountServiceInterfaceMock->expects($this->any())->method('getConfirmationStatus')
            ->withAnyParameters()
            ->will($this->returnValue(CustomerAccountServiceInterface::ACCOUNT_CONFIRMED));
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
                'customerHelper' => $this->customerHelperMock,
                'customerAccountService' => $this->customerAccountServiceInterfaceMock,
                'customerMetadataService' => $this->customerMetadataServiceInterfaceMock,
                'customerBuilder' => $this->customerBuilderMock
            ]
        )->initForm();
    }

    /**
     * Data provider for method testInitForm
     * @return array
     */
    public function getInitFormData()
    {
        return array(
            array([], true, true),
            array(['id' => 1], true, true),
            array([], false, false),
            array(['id' => 1], false, false),
        );
    }
}
