<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;


class TokensDialogTest extends \Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest
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
                    [
                        [
                            \Magento\Integration\Controller\Adminhtml\Integration::PARAM_INTEGRATION_ID,
                            null,
                            self::INTEGRATION_ID, ],
                        [\Magento\Integration\Controller\Adminhtml\Integration::PARAM_REAUTHORIZE, 0, 0],
                    ]
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
                    [
                        [
                            \Magento\Integration\Controller\Adminhtml\Integration::PARAM_INTEGRATION_ID,
                            null,
                            self::INTEGRATION_ID,
                        ],
                        [\Magento\Integration\Controller\Adminhtml\Integration::PARAM_REAUTHORIZE, 0, 1],
                    ]
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
        $consumerMock = $this->getMock(\Magento\Integration\Model\Oauth\Consumer::class, [], [], '' , false);
        $consumerMock->expects($this->once())->method('getId')->willReturn(1);
        $this->_oauthSvcMock->expects($this->once())->method('loadConsumer')->willReturn($consumerMock);

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
