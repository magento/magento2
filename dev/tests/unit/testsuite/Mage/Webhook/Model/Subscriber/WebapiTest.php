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
class Mage_Webhook_Model_Subscriber_WebapiTest extends PHPUnit_Framework_TestCase
{
    const ROLE_ID = "ROLE_ID";

    const REQUIRED_PERMISSION = "REQUIRED_PERMISSION";

    const RANDOM_PERMISSION = "RANDOM_PERMISSION";

    /** @var PHPUnit_Framework_MockObject_MockObject mock version of Mage_Webhook_Model_Subscriber_Webapi */
    protected $_webapi;

    /** @var PHPUnit_Framework_MockObject_MockObject mock version of Mage_Webhook_Model_Subscriber */
    protected $_subscriber;

    public function setUp()
    {
        parent::setUp();

        $this->_subscriber = $this->getMockBuilder('Mage_Webhook_Model_Subscriber')
                ->disableOriginalConstructor()
                ->setMethods(array('getRequiredPermissions', 'getApiUser', 'setStatus', 'save'))
                ->getMock();
        $this->_webapi = $this->getMockBuilder('Mage_Webhook_Model_Subscriber_Webapi')
            ->setMethods(array('_deactivate', '_getAclRuleCollection'))
            ->setConstructorArgs(array($this->_subscriber))->getMock();
    }

    public function provider()
    {
        return array(
            array(
                Mage_Webhook_Model_Subscriber_WebapiTest::REQUIRED_PERMISSION,
                Mage_Webhook_Model_Subscriber_WebapiTest::REQUIRED_PERMISSION, TRUE
            ),
            array(
                Mage_Webhook_Model_Subscriber_WebapiTest::REQUIRED_PERMISSION,
                Mage_Webhook_Model_Subscriber_WebapiTest::RANDOM_PERMISSION, FALSE
            )
        );
    }

    /**
     * @dataProvider provider
     * @param $requiredPermission
     * @param $actualPermission
     * @param $result
     */
    public function testValidate($requiredPermission, $actualPermission, $result)
    {
        $mockUser = $this->getMockBuilder('Mage_Webapi_Model_Acl_User')->disableOriginalConstructor()
                ->setMethods(array('getRoleId'))->getMock();
        $mockUser->expects($this->once())->method('getRoleId')
            ->will($this->returnValue(Mage_Webhook_Model_Subscriber_WebapiTest::ROLE_ID));

        $this->_subscriber->expects($this->once())->method('getRequiredPermissions')
                ->will($this->returnValue(array($requiredPermission)));
        $this->_subscriber->expects($this->once())->method('getApiUser')->will($this->returnValue($mockUser));

        $mockRuleCollection =
                $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_Rule_Collection')->disableOriginalConstructor()
                        ->setMethods(array('addFieldToFilter', 'load', 'toArray'))->getMock();
        $mockRuleCollection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());
        $mockRuleCollection->expects($this->once())->method('load')->will($this->returnSelf());
        $mockRuleCollection->expects($this->once())->method('toArray')
                ->will($this->returnValue(array(array($actualPermission))));

        $this->_webapi->expects($this->once())->method('_getAclRuleCollection')
                ->will($this->returnValue($mockRuleCollection));

        // Deactivate if no match
        if (FALSE == $result) {
            $this->_subscriber->expects($this->once())->method('setStatus')
                ->with(Mage_Webhook_Model_Subscriber::STATUS_INACTIVE);
            $this->_subscriber->expects($this->once())->method('save');
        }

        $this->assertEquals($result, $this->_webapi->validate());
    }
}