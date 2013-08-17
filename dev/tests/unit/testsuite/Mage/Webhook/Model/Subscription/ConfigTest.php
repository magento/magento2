<?php
/**
 * Mage_Webhook_Model_Subscription_Config
 *
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
class Mage_Webhook_Model_Subscription_ConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * String constants representing XML strings to be used in stub config element.
     *
     * Single-quotes are used because phpcs does not handle heredocs well.
     */
    const SETTING_NAME_XML =
                    '<xml>
                        <setting_name>
                            <name>Extension Name</name>
                        </setting_name>
                    </xml>';

    const NAME_MISSING_XML =
                '<xml>
                    <name_missing>
                        <!-- Missing name on purpose -->
                    </name_missing>
                </xml>';

    const VERSION_INCREMENTED_XML =
                '<xml>
                    <setting_name_on_existing_subscription_with_version_incremented>
                        <name>Extension Name</name>
                        <version>0.1</version>
                        <topics>
                            <topic_one>
                                <subcall/>
                            </topic_one>
                        </topics>
                    </setting_name_on_existing_subscription_with_version_incremented>
                </xml>';

    const WITHOUT_VERSION =
                '<xml>
                    <setting_name_on_existing_subscription_without_version>
                        <name>Extension Name</name>
                    </setting_name_on_existing_subscription_without_version>
                </xml>';

    /** @var Mage_Webhook_Model_Subscription_Config that is also our unit under test */
    protected $_config;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockMageConfig;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockSubscribFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockCollection;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockLogger;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockSubscription;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockTranslator;

    public function setUp()
    {
        $this->_mockSubscription = $this->_createMockSubscription();

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

    /**
     * Translates array of errors into string
     *
     * @param array $errors
     * @return string
     */
    public static function translateCallback(array $errors)
    {
        return implode("\n", $errors);
    }

    /**
     * Internal factory for mock subscription, stubs necessary magic methods
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createMockSubscription()
    {
        // We need to define all magic methods.  Once we define any method, we need to define all methods
        // If we don't define any methods, then we can only stub out concrete methods, but not any
        // of the magic methods, since they weren't explicitly defined.
        $methods = array('setData', 'getData', 'unsetData', 'save', 'setName', 'setTopics', 'setFormat',
                         'setEndpointUrl', 'getAuthenticationOptions', 'unsetAuthenticationOption', 'getVersion',
                         'setVersion', 'setAuthenticationType');
        $mock = $this->getMockBuilder('Mage_Webhook_Model_Subscription')
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

    /**
     * Creates stub config element given a fragment of valid xml string
     *
     * @param string $xmlString
     * @return Mage_Core_Model_Config_Element
     */
    protected function _createStubConfigElement($xmlString)
    {
        return new Mage_Core_Model_Config_Element($xmlString);
    }

    /**
     * Initializes a set of mocks and stubs
     *
     * @param Mage_Core_Model_Config_Element          $configNode
     * @param PHPUnit_Framework_MockObject_MockObject $subxCollection
     *        Mocks Mage_Webhook_Model_Resource_Subscription_Collection
     * @param string[]                                $expectedErrors
     */
    protected function _stubMock($configNode, $subxCollection = null, $expectedErrors = null)
    {
        // Mock objects
        $this->_mockCollection = $this->getMockBuilder('Mage_Webhook_Model_Resource_Subscription_Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockMageConfig = $this->getMockBuilder('Mage_Core_Model_Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockSubscribFactory = $this->getMockBuilder('Mage_Webhook_Model_Subscription_Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockLogger = $this->getMockBuilder('Mage_Core_Model_Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockTranslator = $this->getMockBuilder('Mage_Core_Model_Translate')
            ->disableOriginalConstructor()
            ->getMock();

        // Stub create
        $this->_mockSubscribFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_mockSubscription));

        // Stub logException
        if ($expectedErrors !== null) {
            $this->_mockLogger->expects($this->once())
                ->method('logException')
                ->with($this->equalTo(new Mage_Webhook_Exception(implode("\n", $expectedErrors))));
        }

        // Stub getNode
        $this->_mockMageConfig->expects($this->any())
            ->method('getNode')
            ->with($this->equalTo(Mage_Webhook_Model_Subscription_Config::XML_PATH_SUBSCRIPTIONS))
            ->will($this->returnValue($configNode));

        // Get or set subscription collection mock
        if ($subxCollection !== null) {
            $this->_mockCollection = $subxCollection;
        } else {
            $this->_mockCollection = $this->_createMockSubscriptionCollection();
        }

        // Stub translate
        $this->_mockTranslator->expects($this->any())
            ->method('translate')
            ->will($this->returnCallback(array($this, 'translateCallback')));

        // Create config object
        $this->_config = new Mage_Webhook_Model_Subscription_Config(
            $this->_mockTranslator,
            $this->_mockCollection,
            $this->_mockMageConfig,
            $this->_mockSubscribFactory,
            $this->_mockLogger);
    }

    /**
     * Pseudo-factory method for mock subscription collection
     *
     * @param array $idToSubscriptionsMap
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _createMockSubscriptionCollection($idToSubscriptionsMap = array())
    {
        $mock = $this->getMockBuilder('Mage_Webhook_Model_Resource_Subscription_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        // Order matters when mocking out methods.  We need the more restrictive one first.
        foreach ($idToSubscriptionsMap as $id => $subscriptions) {
            $mock->expects($this->any())
                ->method('getSubscriptionsByAlias')
                ->with($this->equalTo($id))
                ->will($this->returnValue($subscriptions));
        }
        // Put the less restrictive stub at the end
        $mock->expects($this->any())
            ->method('getSubscriptionsByAlias')
            ->will($this->returnValue(array()));
        return $mock;
    }

    public function testSettingNameNoSubscription()
    {
        $configNode = $this->_createStubConfigElement(self::SETTING_NAME_XML);

        // Set expectations
        $this->_mockSubscription->expects($this->atLeastOnce())
            ->method('save');
        $this->_mockSubscription->expects($this->atLeastOnce())
            ->method('setName')
            ->with($this->equalTo('Extension Name'));

        $this->_stubMock($configNode);

        // Run test
        $this->_config->updateSubscriptionCollection();
    }

    public function testNameMissing()
    {
        $configNode = $this->_createStubConfigElement(self::NAME_MISSING_XML);

        // Set expectations
        $this->_mockSubscription->expects($this->never())
            ->method('save');
        $this->_mockSubscription->expects($this->never())
            ->method('setName');

        $expectedErrors = array(
            "Invalid config data for subscription '%s'.",
            'name_missing'
        );

        $this->_stubMock($configNode, null, $expectedErrors);

        // Run test
        $this->_config->updateSubscriptionCollection();
    }

    public function testSettingNameVersionIncremented()
    {
        $existingSubscription = $this->_createMockSubscription();
        $existingSubscription->expects($this->atLeastOnce())
            ->method('save');
        $existingSubscription->expects($this->atLeastOnce())
            ->method('setName')
            ->with($this->equalTo('Extension Name'));
        $existingSubscription->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('0.0'));

        $configNode = $this->_createStubConfigElement(self::VERSION_INCREMENTED_XML);
        $subxCollection = $this->_createMockSubscriptionCollection(
            array(
                'setting_name_on_existing_subscription_with_version_incremented' => array(&$existingSubscription)
            ));

        // Set expectations
        $this->_mockSubscription->expects($this->never())
            ->method('save');
        $this->_mockSubscription->expects($this->never())
            ->method('setName');

        $this->_stubMock($configNode, $subxCollection);

        // Run test
        $this->_config->updateSubscriptionCollection();
    }

    public function testSettingNameNoVersion()
    {
        // Make sure we never call save or setName on the existing subscription
        $existingSubscription = $this->_createMockSubscription();
        $existingSubscription->expects($this->never())
            ->method('save');
        $existingSubscription->expects($this->never())
            ->method('setName');

        $configNode = $this->_createStubConfigElement(self::WITHOUT_VERSION);

        // Set expectations
        $this->_mockSubscription->expects($this->never())
            ->method('save');
        $this->_mockSubscription->expects($this->never())
            ->method('setName');

        $subxCollection = $this->_createMockSubscriptionCollection(
            array(
                'setting_name_on_existing_subscription_without_version' => array(&$existingSubscription)
            )
        );

        $this->_stubMock($configNode, $subxCollection);

        // Run test
        $this->_config->updateSubscriptionCollection();
    }
}