<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Framework\Exception\IntegrationException;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration;
use Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest;

class EditTest extends IntegrationTest
{
    public function testEditAction()
    {
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            self::INTEGRATION_ID
        )->willReturn(
            $this->_getSampleIntegrationData()
        );
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            Integration::PARAM_INTEGRATION_ID
        )->willReturn(
            self::INTEGRATION_ID
        );
        // put data in session, the magic function getFormData is called so, must match __call method name
        $this->_backendSessionMock->expects(
            $this->any()
        )->method(
            '__call'
        )->willReturnMap(
            [
                ['setIntegrationData'],
                [
                    'getIntegrationData',
                    [Info::DATA_ID => self::INTEGRATION_ID, Info::DATA_NAME => 'testIntegration']
                ],
            ]
        );
        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        $this->pageTitleMock->expects($this->atLeastOnce())
            ->method('prepend');
        $this->_verifyLoadAndRenderLayout();
        $controller = $this->_createIntegrationController('Edit');
        $controller->execute();
    }

    public function testEditActionNonExistentIntegration()
    {
        $exceptionMessage = 'This integration no longer exists.';
        // verify the error
        $this->_messageManager->expects($this->once())->method('addError')->with($exceptionMessage);
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(self::INTEGRATION_ID);
        // put data in session, the magic function getFormData is called so, must match __call method name
        $this->_backendSessionMock->expects(
            $this->any()
        )->method(
            '__call'
        )->willReturn(
            ['name' => 'nonExistentInt']
        );

        $invalidIdException = new IntegrationException(__($exceptionMessage));
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'get'
        )->willThrowException(
            $invalidIdException
        );
        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        $this->_verifyLoadAndRenderLayout();
        $integrationContr = $this->_createIntegrationController('Edit');
        $integrationContr->execute();
    }

    public function testEditActionNoDataAdd()
    {
        $exceptionMessage = 'Integration ID is not specified or is invalid.';
        // verify the error
        $this->_messageManager->expects($this->once())->method('addError')->with($exceptionMessage);
        $this->_verifyLoadAndRenderLayout();
        $integrationContr = $this->_createIntegrationController('Edit');
        $integrationContr->execute();
    }

    public function testEditException()
    {
        $exceptionMessage = 'Integration ID is not specified or is invalid.';
        // verify the error
        $this->_messageManager->expects($this->once())->method('addError')->with($exceptionMessage);
        $this->_controller = $this->_createIntegrationController('Edit');
        $this->_controller->execute();
    }
}
