<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Framework\View\Layout\Element as LayoutElement;

class PermissionsDialogTest extends \Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest
{
    public function testPermissionsDialog()
    {
        $controller = $this->_createIntegrationController('PermissionsDialog');

        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getParam'
            )->with(
                $this->equalTo(\Magento\Integration\Controller\Adminhtml\Integration::PARAM_INTEGRATION_ID)
            )->will(
                $this->returnValue(self::INTEGRATION_ID)
            );

        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->equalTo(self::INTEGRATION_ID)
            )->will(
                $this->returnValue($this->_getSampleIntegrationData())
            );

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

        $this->_layoutMergeMock->expects(
            $this->once()
        )->method(
                'getFileLayoutUpdatesXml'
            )->will(
                $this->returnValue($layoutUpdates)
            );

        $this->_viewMock->expects(
            $this->once()
        )->method(
                'loadLayout'
            )->with(
                $this->equalTo(['adminhtml_integration_activate_permissions_webapi'])
            );

        $controller->execute();
    }
}
