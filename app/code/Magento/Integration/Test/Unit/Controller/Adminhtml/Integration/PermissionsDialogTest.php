<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\Element as LayoutElement;
use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTestCase;

class PermissionsDialogTest extends IntegrationTestCase
{
    public function testPermissionsDialog()
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                Factory::class,
                $this->createMock(Factory::class)
            ],
            [
                SerializerInterface::class,
                $this->createMock(SerializerInterface::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $controller = $this->_createIntegrationController('PermissionsDialog');

        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->with(Integration::PARAM_INTEGRATION_ID)
            ->willReturn(self::INTEGRATION_ID);

        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with(self::INTEGRATION_ID)
            ->willReturn($this->_getSampleIntegrationData());

        // @codingStandardsIgnoreStart
        $handle = <<<HANDLE
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <handle id="adminhtml_integration_activate_permissions_webapi">
       <referenceBlock name="integration.activate.permissions.tabs">
          <block class="Magento\Integration\Block\Adminhtml\Integration\Activate\Permissions\Tab\Webapi" name="integration_activate_permissions_tabs_webapi" template="integration/activate/permissions/tab/webapi.phtml"/>
          <action method="addTab">
             <argument name="name" xsi:type="string">integration_activate_permissions_tabs_webapi</argument>
             <argument name="block" xsi:type="string">integration_activate_permissions_tabs_webapi</argument>
          </action>
       </referenceBlock>
    </handle>
</layout>
HANDLE;
        // @codingStandardsIgnoreEnd

        $layoutUpdates = new LayoutElement($handle);
        $this->_registryMock->expects($this->any())->method('register');

        $this->_layoutMergeMock->expects($this->once())
            ->method('getFileLayoutUpdatesXml')
            ->willReturn($layoutUpdates);

        $this->_viewMock->expects($this->once())
            ->method('loadLayout')
            ->with(['adminhtml_integration_activate_permissions_webapi']);

        $controller->execute();
    }
}
