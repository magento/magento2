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

namespace Magento\Authz\Service;

use Magento\Authz\Service\AuthorizationV1Test\UserLocatorStub;
use Magento\Authz\Model\UserIdentifier;

/**
 * Authorization service test.
 */
class AuthorizationV1Test extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorizationV1 */
    protected $_service;

    protected function setUp()
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $loggerMock = $this->getMockBuilder('Magento\\Logger')->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->any())->method('logException')->will($this->returnSelf());
        $this->_service = $objectManager->create(
            'Magento\\Authz\\Service\\AuthorizationV1',
            array(
                'userIdentifier' => $this->_createUserIdentifier(UserIdentifier::USER_TYPE_INTEGRATION),
                'logger' => $loggerMock
            )
        );
    }

    /**
     * @param string $userType
     * @param string[] $resources
     * @magentoDbIsolation enabled
     * @dataProvider basicAuthFlowProvider
     */
    public function testBasicAuthFlow($userType, $resources)
    {
        $userIdentifier = $this->_createUserIdentifier($userType);

        /** Preconditions check */
        $this->_ensurePermissionsAreNotGranted($userIdentifier, $resources);

        $this->_service->grantPermissions($userIdentifier, $resources);

        /** Validate that access to the specified resources is granted */
        $this->_ensurePermissionsAreGranted($userIdentifier, $resources);
    }

    public function basicAuthFlowProvider()
    {
        return array(
            'integration' => array(
                'userType' => UserIdentifier::USER_TYPE_INTEGRATION,
                'resources' => array('Magento_Sales::create', 'Magento_Cms::page', 'Magento_Adminhtml::dashboard')
            )
        );
    }

    /**
     * @param string $userType
     * @param string[] $initialResources
     * @param string[] $newResources
     * @magentoDbIsolation enabled
     * @dataProvider changePermissionsProvider
     */
    public function testChangePermissions($userType, $initialResources, $newResources)
    {
        $userIdentifier = $this->_createUserIdentifier($userType);

        $this->_service->grantPermissions($userIdentifier, $initialResources);
        /** Preconditions check */
        $this->_ensurePermissionsAreGranted($userIdentifier, $initialResources);
        $this->_ensurePermissionsAreNotGranted($userIdentifier, $newResources);

        $this->_service->grantPermissions($userIdentifier, $newResources);

        /** Check the results of permissions change */
        $this->_ensurePermissionsAreGranted($userIdentifier, $newResources);
        $this->_ensurePermissionsAreNotGranted($userIdentifier, $initialResources);
    }

    public function changePermissionsProvider()
    {
        return array(
            'integration' => array(
                'userType' => UserIdentifier::USER_TYPE_INTEGRATION,
                'initialResources' => array('Magento_Cms::page', 'Magento_Adminhtml::dashboard'),
                'newResources' => array('Magento_Sales::cancel', 'Magento_Cms::page_delete')
            ),
            'integration clear permissions' => array(
                'userType' => UserIdentifier::USER_TYPE_INTEGRATION,
                'initialResources' => array('Magento_Sales::capture', 'Magento_Cms::page_delete'),
                'newResources' => array(),
            ),
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testIsAllowedArrayOfResources()
    {
        $userIdentifier = $this->_createUserIdentifier(UserIdentifier::USER_TYPE_INTEGRATION);
        $resources = array('Magento_Cms::page', 'Magento_Adminhtml::dashboard');
        $this->_service->grantPermissions($userIdentifier, $resources);
        /** Preconditions check */
        $this->_ensurePermissionsAreGranted($userIdentifier, $resources);

        /** Ensure that permissions check to multiple resources at once works as expected */
        $this->assertTrue(
            $this->_service->isAllowed($resources, $userIdentifier),
            'Access to multiple resources is expected to be granted, but is prohibited.'
        );
        $this->assertFalse(
            $this->_service->isAllowed(array_merge($resources, array('invalid_resource')), $userIdentifier),
            'Access is expected to be denied when at least one of the resources is unavailable.'
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetAllowedResources()
    {
        $userIdentifierA = $this->_createUserIdentifier(UserIdentifier::USER_TYPE_INTEGRATION);
        $resourcesA = array('Magento_Adminhtml::dashboard', 'Magento_Cms::page');
        $this->_service->grantPermissions($userIdentifierA, $resourcesA);

        $userIdentifierB = $this->_createUserIdentifier(UserIdentifier::USER_TYPE_INTEGRATION);
        $resourcesB = array('Magento_Cms::block', 'Magento_Sales::cancel');
        $this->_service->grantPermissions($userIdentifierB, $resourcesB);

        /** Preconditions check */
        $this->_ensurePermissionsAreGranted($userIdentifierA, $resourcesA);
        $this->_ensurePermissionsAreGranted($userIdentifierB, $resourcesB);

        $this->assertEquals(
            $resourcesA,
            $this->_service->getAllowedResources($userIdentifierA),
            "The list of resources allowed to the user is invalid."
        );

        $this->assertEquals(
            $resourcesB,
            $this->_service->getAllowedResources($userIdentifierB),
            "The list of resources allowed to the user is invalid."
        );
    }

    /**
     * @expectedException \Magento\Service\Exception
     * @expectedMessage The role associated with the specified user cannot be found.
     */
    public function testGetAllowedResourcesRoleNotFound()
    {
        $userIdentifier = $this->_createUserIdentifier(UserIdentifier::USER_TYPE_INTEGRATION);
        $this->_service->getAllowedResources($userIdentifier);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGrantAllPermissions()
    {
        $userIdentifier = $this->_createUserIdentifier(UserIdentifier::USER_TYPE_INTEGRATION);
        $this->_service->grantAllPermissions($userIdentifier);
        $this->_ensurePermissionsAreGranted($userIdentifier, array('Magento_Adminhtml::all'));
    }

    /**
     * Create new User identifier
     *
     * @param string $userType
     * @return UserIdentifier
     */
    protected function _createUserIdentifier($userType)
    {
        $userId = ($userType == UserIdentifier::USER_TYPE_GUEST) ? 0 : rand(1, 1000);
        $userLocatorStub = new UserLocatorStub();
        return new UserIdentifier($userLocatorStub, $userType, $userId);
    }

    /**
     * Check if user has access to the specified resources.
     *
     * @param UserIdentifier $userIdentifier
     * @param string[] $resources
     */
    protected function _ensurePermissionsAreGranted($userIdentifier, $resources)
    {
        foreach ($resources as $resource) {
            $this->assertTrue(
                $this->_service->isAllowed($resource, $userIdentifier),
                "Access to resource '{$resource}' is prohibited while it is expected to be granted."
            );
        }
    }

    /**
     * Check if access to the specified resources is prohibited to the user.
     *
     * @param UserIdentifier $userIdentifier
     * @param string[] $resources
     */
    protected function _ensurePermissionsAreNotGranted($userIdentifier, $resources)
    {
        foreach ($resources as $resource) {
            $this->assertFalse(
                $this->_service->isAllowed($resource, $userIdentifier),
                "Access to resource '{$resource}' is expected to be prohibited."
            );
        }
    }
}
