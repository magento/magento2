<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Framework\Exception\IntegrationException;

class EditTest extends \Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest
{
    public function testEditAction()
    {
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->equalTo(self::INTEGRATION_ID)
            )->will(
                $this->returnValue($this->_getSampleIntegrationData())
            );
        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getParam'
            )->with(
                $this->equalTo(\Magento\Integration\Controller\Adminhtml\Integration::PARAM_INTEGRATION_ID)
            )->will(
                $this->returnValue(self::INTEGRATION_ID)
            );
        // put data in session, the magic function getFormData is called so, must match __call method name
        $this->_backendSessionMock->expects(
            $this->any()
        )->method(
                '__call'
            )->will(
                $this->returnValueMap(
                    [
                        ['setIntegrationData'],
                        [
                            'getIntegrationData',
                            [Info::DATA_ID => self::INTEGRATION_ID, Info::DATA_NAME => 'testIntegration']
                        ],
                    ]
                )
            );
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
        $this->_messageManager->expects($this->once())->method('addError')->with($this->equalTo($exceptionMessage));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(self::INTEGRATION_ID));
        // put data in session, the magic function getFormData is called so, must match __call method name
        $this->_backendSessionMock->expects(
            $this->any()
        )->method(
                '__call'
            )->will(
                $this->returnValue(['name' => 'nonExistentInt'])
            );

        $invalidIdException = new IntegrationException(__($exceptionMessage));
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->will(
                $this->throwException($invalidIdException)
            );
        $this->_verifyLoadAndRenderLayout();
        $integrationContr = $this->_createIntegrationController('Edit');
        $integrationContr->execute();
    }

    public function testEditActionNoDataAdd()
    {
        $exceptionMessage = 'Integration ID is not specified or is invalid.';
        // verify the error
        $this->_messageManager->expects($this->once())->method('addError')->with($this->equalTo($exceptionMessage));
        $this->_verifyLoadAndRenderLayout();
        $integrationContr = $this->_createIntegrationController('Edit');
        $integrationContr->execute();
    }

    public function testEditException()
    {
        $exceptionMessage = 'Integration ID is not specified or is invalid.';
        // verify the error
        $this->_messageManager->expects($this->once())->method('addError')->with($this->equalTo($exceptionMessage));
        $this->_controller = $this->_createIntegrationController('Edit');
        $this->_controller->execute();
    }
}
