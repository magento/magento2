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

use Magento\Integration\Model\Integration as IntegrationModel;

class TokensDialogTest extends \Magento\Integration\Controller\Adminhtml\IntegrationTest
{
    public function testTokensDialog()
    {
        $controller = $this->_createIntegrationController('TokensDialog');
        $this->_registryMock->expects($this->any())->method('register');

        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getParam'
            )->will(
                $this->returnValueMap(
                    array(
                        array(
                            \Magento\Integration\Controller\Adminhtml\Integration::PARAM_INTEGRATION_ID,
                            null,
                            self::INTEGRATION_ID),
                        array(\Magento\Integration\Controller\Adminhtml\Integration::PARAM_REAUTHORIZE, 0, 0)
                    )
                )
            );

        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->equalTo(self::INTEGRATION_ID)
            )->will(
                $this->returnValue($this->_getIntegrationModelMock())
            );

        $this->_oauthSvcMock->expects($this->once())->method('createAccessToken')->will($this->returnValue(true));

        $this->_viewMock->expects($this->any())->method('loadLayout');
        $this->_viewMock->expects($this->any())->method('renderLayout');

        $controller->execute();
    }

    public function testTokensExchangeReauthorize()
    {
        $controller = $this->_createIntegrationController('TokensExchange');

        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getParam'
            )->will(
                $this->returnValueMap(
                    array(
                        array(
                            \Magento\Integration\Controller\Adminhtml\Integration::PARAM_INTEGRATION_ID,
                            null,
                            self::INTEGRATION_ID
                        ),
                        array(\Magento\Integration\Controller\Adminhtml\Integration::PARAM_REAUTHORIZE, 0, 1)
                    )
                )
            );

        $this->_integrationSvcMock->expects(
            $this->once()
        )->method(
                'get'
            )->with(
                $this->equalTo(self::INTEGRATION_ID)
            )->will(
                $this->returnValue($this->_getIntegrationModelMock())
            );

        $this->_oauthSvcMock->expects($this->once())->method('deleteIntegrationToken');
        $this->_oauthSvcMock->expects($this->once())->method('postToConsumer');

        $this->_messageManager->expects($this->once())->method('addNotice');
        $this->_messageManager->expects($this->never())->method('addError');
        $this->_messageManager->expects($this->never())->method('addSuccess');

        $this->_viewMock->expects($this->once())->method('loadLayout');
        $this->_viewMock->expects($this->once())->method('renderLayout');

        $this->_responseMock->expects($this->once())->method('getBody');
        $this->_responseMock->expects($this->once())->method('representJson');

        $controller->execute();
    }
}
