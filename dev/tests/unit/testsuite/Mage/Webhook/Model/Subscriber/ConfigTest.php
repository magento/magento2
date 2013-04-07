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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Subscriber_ConfigTest extends PHPUnit_Framework_TestCase
{

    const PARAM_CONFIG_NODE = '_getSubscriberConfigNode';
    const PARAM_SUBSCRIBER_COLLECTION = '_getSubscriberCollection';
    const PARAM_EXPECTED_ERRORS = 'expectedErrors';
    const PARAM_VALIDATION = 'validation';

    const KEY_CREATED_SUBSCRIBER = 'subscriber';
    const KEY_CONFIG = 'config';

    const INVOKE_METHOD = 'method';
    const INVOKE_EXPECTS = 'expects';
    const INVOKE_WITH_ARGS = 'with_args';
    const INVOKE_WITH_ARG = 'with_arg';
    const PARAM_MOCK_OBJECTS_TO_REGISTER = 'mock_objects_to_register';


    /** @var PHPUnit_Framework_MockObject_MockObject mock version of Mage_Webhook_Model_Subscriber_Config
     *  that is also our unit under test */
    protected $_config;

    /** @var PHPUnit_Framework_MockObject_MockObject mock version of Mage_Webhook_Model_Subscriber */
    protected $_subscriber;

    protected $_mockObjects = array();

    public function setUp()
    {
        $this->_config = $this->_createMockUnitUnderTest();
        $this->_subscriber = $this->_createMockSubscriber();
        $this->_mockHelperData();
    }

    /**
     * @dataProvider dataProviderForTestUpdateSubscriberCollection
     */
    public function testUpdateSubscriberCollection($params)
    {
        $this->_registerMocks($params);

        $this->_stubMock($params);

        $this->_setExpectations($params);

        // Run test
        $this->_config->updateSubscriberCollection();
    }


    public function dataProviderForTestUpdateSubscriberCollection()
    {
        return array(
            array($this->_getSettingNameTest()),
            array($this->_getNameMissingTest()),
            array($this->_getSettingNameOnExistingSubscriberWithVersionIncrementedTest()),
            array($this->_getSettingNameOnExistingSubscriberWithoutVersionTest()),
        );
    }

    protected function _getSettingNameTest()
    {
        return array(
            self::PARAM_CONFIG_NODE => $this->_createStubConfigElement(
                <<<XML
                <xml>
                    <setting_name>
                        <name>Extension Name</name>
                        <mapping>custom</mapping>
                    </setting_name>
                </xml>
XML
            ),
            self::PARAM_VALIDATION => array(
                self::KEY_CREATED_SUBSCRIBER => array(
                    array(
                        self::INVOKE_METHOD => 'save',
                        self::INVOKE_EXPECTS => $this->atLeastOnce(),
                    ),
                    array(
                        self::INVOKE_METHOD => 'setName',
                        self::INVOKE_EXPECTS => $this->atLeastOnce(),
                        self::INVOKE_WITH_ARG => $this->equalTo('Extension Name'),
                    ),
                    array(
                        self::INVOKE_METHOD => 'setMapping',
                        self::INVOKE_EXPECTS => $this->atLeastOnce(),
                        self::INVOKE_WITH_ARG => $this->equalTo('custom'),
                    ),
                ),
            ),
        );
    }

    protected function _getNameMissingTest()
    {
        return array(
            self::PARAM_CONFIG_NODE => $this->_createStubConfigElement(
                <<<XML
                <xml>
                    <name_missing>
                        <!-- Missing name on purpose -->
                    </name_missing>
                </xml>
XML
            ),
            self::PARAM_VALIDATION => array(
                self::KEY_CREATED_SUBSCRIBER => array(
                    array(
                        self::INVOKE_METHOD => 'save',
                        self::INVOKE_EXPECTS => $this->never(),
                    ),
                    array(
                        self::INVOKE_METHOD => 'setName',
                        self::INVOKE_EXPECTS => $this->never(),
                    ),
                ),
            ),
            self::PARAM_EXPECTED_ERRORS => array(
                "Invalid config data for subscriber '%s'."
            )
        );
    }

    protected function _getSettingNameOnExistingSubscriberWithVersionIncrementedTest()
    {
        $existingSubscriber = $this->_createMockSubscriber();
        $existingSubscriber->expects($this->atLeastOnce())
            ->method('save');
        $existingSubscriber->expects($this->atLeastOnce())
            ->method('setName')
            ->with($this->equalTo('Extension Name'));
        $existingSubscriber->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('0.0'));

        return array(
            self::PARAM_CONFIG_NODE => $this->_createStubConfigElement(
                <<<XML
                <xml>
                    <setting_name_on_existing_subscriber_with_version_incremented>
                        <name>Extension Name</name>
                        <version>0.1</version>
                    </setting_name_on_existing_subscriber_with_version_incremented>
                </xml>
XML
            ),
            self::PARAM_SUBSCRIBER_COLLECTION => $this->_createMockSubscriberCollection(
                array(
                    'setting_name_on_existing_subscriber_with_version_incremented' => array(&$existingSubscriber)
                )
            ),
            self::PARAM_VALIDATION => array(
                // Make sure we never call the subscriber that's created by the createSubscriber method
                self::KEY_CREATED_SUBSCRIBER => array(
                    array(
                        self::INVOKE_METHOD => 'save',
                        self::INVOKE_EXPECTS => $this->never(),
                    ),
                    array(
                        self::INVOKE_METHOD => 'setName',
                        self::INVOKE_EXPECTS => $this->never(),
                    ),
                ),
            ),
            self::PARAM_MOCK_OBJECTS_TO_REGISTER => array(
                &$existingSubscriber
            ),
        );
    }

    protected function _getSettingNameOnExistingSubscriberWithoutVersionTest()
    {
        // Make sure we never call save or setName on the existing subscriber
        $existingSubscriber = $this->_createMockSubscriber();
        $existingSubscriber->expects($this->never())
            ->method('save');
        $existingSubscriber->expects($this->never())
            ->method('setName');

        return array(
            self::PARAM_CONFIG_NODE => $this->_createStubConfigElement(
                <<<XML
                <xml>
                    <setting_name_on_existing_subscriber_without_version>
                        <name>Extension Name</name>
                    </setting_name_on_existing_subscriber_without_version>
                </xml>
XML
            ),
            self::PARAM_SUBSCRIBER_COLLECTION => $this->_createMockSubscriberCollection(
                array(
                    'setting_name_on_existing_subscriber_without_version' => array(&$existingSubscriber)
                )
            ),
            self::PARAM_VALIDATION => array(
                // Make sure we never call the subscriber that's created by the createSubscriber method
                self::KEY_CREATED_SUBSCRIBER => array(
                    array(
                        self::INVOKE_METHOD => 'save',
                        self::INVOKE_EXPECTS => $this->never(),
                    ),
                    array(
                        self::INVOKE_METHOD => 'setName',
                        self::INVOKE_EXPECTS => $this->never(),
                    ),
                ),
            ),
            self::PARAM_MOCK_OBJECTS_TO_REGISTER => array(
                &$existingSubscriber
            ),
        );
    }

    protected function _retrieveMock($key)
    {
        switch ($key) {
            case self::KEY_CREATED_SUBSCRIBER:
                return $this->_subscriber;
            case self::KEY_CONFIG:
                return $this->_config;
            default:
                throw new InvalidArgumentException($key);
        }
    }

    protected function _createMockUnitUnderTest()
    {
        $mock = $this->getMock('Mage_Webhook_Model_Subscriber_Config', array(
            '_getSubscriberConfigNode',
            '_getSubscriberCollection',
            '_handleErrors',
            '_createSubscriber',
        ));

        return $mock;
    }

    protected function _createMockSubscriberCollection($idToSubscribersMap = array())
    {
        $mock = $this->getMockBuilder('Mage_Webhook_Model_Resource_Subscriber_Collection')
            ->disableOriginalConstructor()
            ->getMock();

        // Order matters when mocking out methods.  We need the more restrictive one first.
        foreach ($idToSubscribersMap as $id => $subscribers) {
            $mock->expects($this->any())
                ->method('getItemsByColumnValue')
                ->with('extension_id', $this->equalTo($id))
                ->will($this->returnValue($subscribers));
        }

        // Put the less restrictive stub at the end
        $mock->expects($this->any())
            ->method('getItemsByColumnValue')
            ->will($this->returnValue(array()));

        return $mock;
    }

    protected function _createMockSubscriber()
    {
        // We need to define all magic methods.  Once we define any method, we need to define all methods
        // If we don't define any methods, then we can only stub out concrete methods, but not any
        // of the magic methods, since they weren't explicitly defined.
        $methods = array('setData', 'getData', 'unsetData', 'save', 'setName', 'setMapping', 'setTopics',
            'getAuthenticationOptions', 'unsetAuthenticationOption', 'getVersion', 'setVersion');
        $mock = $this->getMockBuilder('Mage_Webhook_Model_Subscriber')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        foreach ($methods as $method) {
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnSelf());
        }

        return $mock;
    }

    protected function _createStubConfigElement($xmlString)
    {
        return new Mage_Core_Model_Config_Element($xmlString);
    }


    protected function _mockHelperData()
    {
        // Hack needed since _config isn't set in Mage
        Mage::unregister('_helper/Mage_Webhook_Helper_Data');
        $mockHelper = $this->getMockBuilder('Mage_Webhook_Helper_Data')
            ->disableOriginalConstructor()
            ->getMock();
        $mockHelper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        Mage::register('_helper/Mage_Webhook_Helper_Data', $mockHelper);
    }

    protected function _stubMock($params)
    {
        $this->_stubGetSubscriberConfigNode($params);

        $this->_stubGetSubscriberCollection($params);

        $this->_stubHandleErrors($params);

        $this->_stubCreateSubscriber();
    }

    protected function _stubCreateSubscriber()
    {
        $this->_config->expects($this->any())
            ->method('_createSubscriber')
            ->will($this->returnValue($this->_subscriber));
    }


    protected function _stubGetSubscriberConfigNode($params)
    {
        $configNode = $params[self::PARAM_CONFIG_NODE];
        $this->_config->expects($this->any())
            ->method('_getSubscriberConfigNode')
            ->will($this->returnValue($configNode));
    }

    protected function _stubGetSubscriberCollection($params)
    {
        if (isset($params[self::PARAM_SUBSCRIBER_COLLECTION])) {
            $subscriberCollection = $params[self::PARAM_SUBSCRIBER_COLLECTION];
        } else {
            $subscriberCollection = $this->_createMockSubscriberCollection();
        }
        $this->_config->expects($this->any())
            ->method('_getSubscriberCollection')
            ->will($this->returnValue($subscriberCollection));
    }

    protected function _stubHandleErrors($params)
    {
        if (isset($params[self::PARAM_EXPECTED_ERRORS])) {
            $expectedErrors = $params[self::PARAM_EXPECTED_ERRORS];
            $this->_config->expects($this->once())
                ->method('_handleErrors')
                ->with($this->equalTo($expectedErrors));
        }
    }


    protected function _setExpectations($params)
    {
        foreach ($params[self::PARAM_VALIDATION] as $key => $expectations) {
            $mock = $this->_retrieveMock($key);

            foreach ($expectations as $expectation) {
                $this->_attachExpectationToMock($expectation, $mock);
            }
        }
    }

    protected function _attachExpectationToMock($expectation, $mock)
    {
        $invocationMocker = $mock->expects($expectation[self::INVOKE_EXPECTS])
            ->method($expectation[self::INVOKE_METHOD]);
        if (isset($expectation[self::INVOKE_WITH_ARG])) {
            $invocationMocker->with($expectation[self::INVOKE_WITH_ARG]);
        } elseif (isset($expectation[self::INVOKE_WITH_ARGS])) {
            call_user_func_array(array($invocationMocker, 'with'), $expectation[self::INVOKE_WITH_ARGS]);
        }
    }

    /**
     * Overriding this method so we can register mocks that were setup during the data provider step.
     * Sadly our parent class has no protected interface for adding to their private mockObjects array,
     * so we need to create our own copy of the parent code and run that in addition to the parent method.
     */
    protected function verifyMockObjects()
    {
        parent::verifyMockObjects();
        foreach ($this->_mockObjects as $mockObject) {
            if ($mockObject->__phpunit_hasMatchers()) {
                $this->addToAssertionCount(1);
            }

            $mockObject->__phpunit_verify();
            $mockObject->__phpunit_cleanup();
        }

        $this->_mockObjects = array();
    }

    protected function _registerMocks($params)
    {
        if (isset($params[self::PARAM_MOCK_OBJECTS_TO_REGISTER])) {
            foreach ($params[self::PARAM_MOCK_OBJECTS_TO_REGISTER] as $mock) {
                $this->_registerMock($mock);
            }
        }
    }

    protected function _registerMock($mock)
    {
        $this->_mockObjects[] = $mock;
    }

}