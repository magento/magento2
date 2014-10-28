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

namespace Magento\Integration\Service\V1;

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
        $loggerMock = $this->getMockBuilder('Magento\\Framework\\Logger')->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->any())->method('logException')->will($this->returnSelf());
        $this->_service = $objectManager->create(
            'Magento\Integration\Service\V1\AuthorizationService',
            array(
                'logger' => $loggerMock
            )
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
        $resources = array('Magento_Sales::create', 'Magento_Cms::page', 'Magento_Adminhtml::dashboard');
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
        return array(
            'integration' => array(
                'integrationId' => rand(1, 1000),
                'initialResources' => array('Magento_Cms::page', 'Magento_Adminhtml::dashboard'),
                'newResources' => array('Magento_Sales::cancel', 'Magento_Cms::page_delete')
            ),
            'integration clear permissions' => array(
                'integrationId' => rand(1, 1000),
                'initialResources' => array('Magento_Sales::capture', 'Magento_Cms::page_delete'),
                'newResources' => array()
            )
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGrantAllPermissions()
    {
        $integrationId = rand(1, 1000);
        $this->_service->grantAllPermissions($integrationId);
        $this->_ensurePermissionsAreGranted($integrationId, array('Magento_Adminhtml::all'));
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
