<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Framework\View\Layout\Element as LayoutElement;

class PermissionsDialogTest extends \Magento\Integration\Controller\Adminhtml\IntegrationTest
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
          <block class="Magento\Webapi\Block\Adminhtml\Integration\Activate\Permissions\Tab\Webapi" name="integration_activate_permissions_tabs_webapi" template="integration/activate/permissions/tab/webapi.phtml"/>
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
                $this->equalTo(array('adminhtml_integration_activate_permissions_webapi'))
            );

        $controller->execute();
    }
}
