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
namespace Magento\Authz\Model;

use Magento\Authz\Model\UserIdentifier;

/**
 * Tests for User identifier.
 */
class UserIdentifierTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_userLocatorMock;

    protected function setUp()
    {
        parent::setUp();
        $this->_userLocatorMock = $this->getMock(
            'Magento\Authz\Model\UserLocatorInterface',
            array('getUserId', 'getUserType')
        );
    }

    /**
     * @param string $userType
     * @param int $userId
     * @dataProvider constructProvider
     */
    public function testConstruct($userType, $userId)
    {
        $context = new UserIdentifier($this->_userLocatorMock, $userType, $userId);
        $this->assertEquals($userId, $context->getUserId());
        $this->assertEquals($userType, $context->getUserType());
    }

    /**
     * @param string $userType
     * @param int $userId
     * @param string $exceptionMessage
     * @dataProvider constructProviderInvalidData
     */
    public function testConstructInvalidData($userType, $userId, $exceptionMessage)
    {
        $this->setExpectedException('\LogicException', $exceptionMessage);
        new UserIdentifier($this->_userLocatorMock, $userType, $userId);
    }

    public function constructProvider()
    {
        return array(
            array(UserIdentifier::USER_TYPE_GUEST, 0),
            array(UserIdentifier::USER_TYPE_CUSTOMER, 1),
            array(UserIdentifier::USER_TYPE_ADMIN, 2),
            array(UserIdentifier::USER_TYPE_INTEGRATION, 3)
        );
    }

    public function constructProviderInvalidData()
    {
        return array(
            array(
                'InvalidUserType',
                1,
                'Invalid user type: \'InvalidUserType\'. Allowed types: Guest, Customer, Admin, Integration'
            ),
            array(UserIdentifier::USER_TYPE_CUSTOMER, -1, 'Invalid user ID: \'-1\''),
            array(UserIdentifier::USER_TYPE_ADMIN, 'InvalidUserId', 'Invalid user ID: \'InvalidUserId\''),
            array(UserIdentifier::USER_TYPE_GUEST, 3, 'Guest user must not have user ID set.')
        );
    }
}
