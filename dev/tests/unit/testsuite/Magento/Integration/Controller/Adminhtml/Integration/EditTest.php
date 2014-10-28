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

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;

class EditTest extends \Magento\Integration\Controller\Adminhtml\IntegrationTest
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
                    array(
                        array('setIntegrationData'),
                        array(
                            'getIntegrationData',
                            array(Info::DATA_ID => self::INTEGRATION_ID, Info::DATA_NAME => 'testIntegration')
                        )
                    )
                )
            );
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
                $this->returnValue(array('name' => 'nonExistentInt'))
            );

        $invalidIdException = new \Magento\Integration\Exception($exceptionMessage);
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
