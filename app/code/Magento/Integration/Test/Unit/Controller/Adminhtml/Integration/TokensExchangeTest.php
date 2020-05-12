<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Model\Oauth\Consumer;
use Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest;

class TokensExchangeTest extends IntegrationTest
{
    public function testTokensExchangeReauthorize()
    {
        $controller = $this->_createIntegrationController('TokensExchange');

        $this->_escaper->expects($this->once())->method('escapeHtml')->willReturnArgument(0);

        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    [
                        Integration::PARAM_INTEGRATION_ID,
                        null,
                        self::INTEGRATION_ID,
                    ],
                    [Integration::PARAM_REAUTHORIZE, 0, 1],
                ]
            );

        $this->_integrationSvcMock->expects($this->once())
            ->method('get')
            ->with(self::INTEGRATION_ID)
            ->willReturn($this->_getIntegrationModelMock());

        $this->_oauthSvcMock->expects($this->once())->method('deleteIntegrationToken');
        $this->_oauthSvcMock->expects($this->once())->method('postToConsumer');
        $consumerMock = $this->createMock(Consumer::class);
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
