<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Model\Integration as IntegrationModel;

class SaveTest extends \Magento\Integration\Controller\Adminhtml\IntegrationTest
{
    public function testSaveAction()
    {
        // Use real translate model
        $this->_translateModelMock = null;
        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getPost'
            )->will(
                $this->returnValue(
                    [
                        IntegrationController::PARAM_INTEGRATION_ID => self::INTEGRATION_ID,
                    ]
                )
            );
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(self::INTEGRATION_ID));
        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                self::INTEGRATION_ID
            )->will(
                $this->returnValue($intData)
            );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'update'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        // verify success message
        $this->_messageManager->expects(
            $this->once()
        )->method(
                'addSuccess'
            )->with(
                __('The integration \'%1\' has been saved.', $intData[Info::DATA_NAME])
            );
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    public function testSaveActionException()
    {
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(self::INTEGRATION_ID));

        // Have integration service throw an exception to test exception path
        $exceptionMessage = 'Internal error. Check exception log for details.';
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                self::INTEGRATION_ID
            )->will(
                $this->throwException(new \Magento\Framework\Model\Exception($exceptionMessage))
            );
        // Verify error
        $this->_messageManager->expects($this->once())->method('addError')->with($this->equalTo($exceptionMessage));
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    public function testSaveActionIntegrationException()
    {
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(self::INTEGRATION_ID));

        // Have integration service throw an exception to test exception path
        $exceptionMessage = 'Internal error. Check exception log for details.';
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                self::INTEGRATION_ID
            )->will(
                $this->throwException(new \Magento\Integration\Exception($exceptionMessage))
            );
        // Verify error
        $this->_messageManager->expects($this->once())->method('addError')->with($this->equalTo($exceptionMessage));
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    public function testSaveActionNew()
    {
        $integration = $this->_getSampleIntegrationData();
        //No id when New Integration is Post-ed
        $integration->unsetData([IntegrationModel::ID, 'id']);
        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getPost'
            )->will(
                $this->returnValue($integration->getData())
            );
        $integration->setData('id', self::INTEGRATION_ID);
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'create'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($integration)
            );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                self::INTEGRATION_ID
            )->will(
                $this->returnValue(null)
            );
        // Use real translate model
        $this->_translateModelMock = null;
        // verify success message
        $this->_messageManager->expects(
            $this->once()
        )->method(
                'addSuccess'
            )->with(
                __('The integration \'%1\' has been saved.', $integration->getName())
            );
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    public function testSaveActionExceptionDuringServiceCreation()
    {
        $exceptionMessage = 'Service could not be saved.';
        $integration = $this->_getSampleIntegrationData();
        // No id when New Integration is Post-ed
        $integration->unsetData([IntegrationModel::ID, 'id']);
        $this->_requestMock->expects(
            $this->any()
        )->method(
                'getPost'
            )->will(
                $this->returnValue($integration->getData())
            );
        $integration->setData('id', self::INTEGRATION_ID);
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'create'
            )->with(
                $this->anything()
            )->will(
                $this->throwException(new \Magento\Integration\Exception($exceptionMessage))
            );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                self::INTEGRATION_ID
            )->will(
                $this->returnValue(null)
            );
        // Use real translate model
        $this->_translateModelMock = null;
        // Verify success message
        $this->_messageManager->expects($this->once())->method('addError')->with($exceptionMessage);
        $integrationController = $this->_createIntegrationController('Save');
        $integrationController->execute();
    }
}
