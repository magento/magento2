<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTestCase;

class TokensDialogTest extends IntegrationTestCase
{
    public function testTokensDialog()
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
        $controller = $this->_createIntegrationController('TokensDialog');
        $this->_registryMock->expects($this->any())->method('register');

        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->willReturnMap(
            [
                [
                    Integration::PARAM_INTEGRATION_ID,
                    null,
                    self::INTEGRATION_ID
                ],[Integration::PARAM_REAUTHORIZE, 0, 0],
            ]
        );

        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            self::INTEGRATION_ID
        )->willReturn(
            $this->_getIntegrationModelMock()
        );

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->_oauthSvcMock->expects($this->once())->method('createAccessToken')->willReturn(true);

        $this->_viewMock->expects($this->any())->method('loadLayout');
        $this->_viewMock->expects($this->any())->method('renderLayout');

        $controller->execute();
    }
}
