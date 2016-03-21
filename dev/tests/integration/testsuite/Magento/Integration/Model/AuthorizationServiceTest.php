<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Integration authorization service test.
 */
class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorizationService */
    protected $_service;

    /** @var \Magento\Framework\Authorization */
    protected $libAuthorization;

    /** @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $userContextMock;

    protected function setUp()
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $loggerMock = $this->getMockBuilder('Psr\\Log\\LoggerInterface')->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->any())->method('critical')->will($this->returnSelf());
        $this->_service = $objectManager->create(
            'Magento\Integration\Model\AuthorizationService',
            [
                'logger' => $loggerMock
            ]
        );

        $this->userContextMock = $this->getMockForAbstractClass('Magento\Authorization\Model\UserContextInterface');
        $this->userContextMock
            ->expects($this->any())
            ->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_INTEGRATION));
        $roleLocator = $objectManager->create(
            'Magento\Webapi\Model\WebapiRoleLocator',
            ['userContext' => $this->userContextMock]
        );
        $this->libAuthorization = $objectManager->create(
            'Magento\Framework\Authorization',
            ['roleLocator' => $roleLocator]
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGrantPermissions()
    {
        $integrationId = rand(1, 1000);
        $resources = ['Magento_Sales::create', 'Magento_Cms::page', 'Magento_Backend::dashboard'];
        /** Preconditions check */
        $this->_ensurePermissionsAreNotGranted($integrationId, $resources);

        $this->_service->grantPermissions($integrationId, $resources);

        /** Validate that access to the specified resources is granted */
        $this->_ensurePermissionsAreGranted($integrationId, $resources);
    }

    /**
     * @param int $integrationId
     * @param string[] $initialResources
     * @param string[] $newResources
     * @magentoDbIsolation enabled
     * @dataProvider changePermissionsProvider
     */
    public function testChangePermissions($integrationId, $initialResources, $newResources)
    {
        $this->_service->grantPermissions($integrationId, $initialResources);
        /** Preconditions check */
        $this->_ensurePermissionsAreGranted($integrationId, $initialResources);
        $this->_ensurePermissionsAreNotGranted($integrationId, $newResources);

        $this->_service->grantPermissions($integrationId, $newResources);

        /** Check the results of permissions change */
        $this->_ensurePermissionsAreGranted($integrationId, $newResources);
        $this->_ensurePermissionsAreNotGranted($integrationId, $initialResources);
    }

    public function changePermissionsProvider()
    {
        return [
            'integration' => [
                'integrationId' => rand(1, 1000),
                'initialResources' => ['Magento_Cms::page', 'Magento_Backend::dashboard'],
                'newResources' => ['Magento_Sales::cancel', 'Magento_Cms::page_delete'],
            ],
            'integration clear permissions' => [
                'integrationId' => rand(1, 1000),
                'initialResources' => ['Magento_Sales::capture', 'Magento_Cms::page_delete'],
                'newResources' => [],
            ]
        ];
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGrantAllPermissions()
    {
        $integrationId = rand(1, 1000);
        $this->_service->grantAllPermissions($integrationId);
        $this->_ensurePermissionsAreGranted($integrationId, ['Magento_Backend::all']);
    }

    /**
     * Check if user has access to the specified resources.
     *
     * @param int $integrationId
     * @param string[] $resources
     */
    protected function _ensurePermissionsAreGranted($integrationId, $resources)
    {
        $this->userContextMock
            ->expects($this->any())
            ->method('getUserId')
            ->will($this->returnValue($integrationId));
        foreach ($resources as $resource) {
            $this->assertTrue(
                $this->libAuthorization->isAllowed($resource),
                "Access to resource '{$resource}' is prohibited while it is expected to be granted."
            );
        }
    }

    /**
     * Check if access to the specified resources is prohibited to the user.
     *
     * @param int $integrationId
     * @param string[] $resources
     */
    protected function _ensurePermissionsAreNotGranted($integrationId, $resources)
    {
        $this->userContextMock
            ->expects($this->any())
            ->method('getUserId')
            ->will($this->returnValue($integrationId));
        foreach ($resources as $resource) {
            $this->assertFalse(
                $this->libAuthorization->isAllowed($resource),
                "Access to resource '{$resource}' is expected to be prohibited."
            );
        }
    }
}
