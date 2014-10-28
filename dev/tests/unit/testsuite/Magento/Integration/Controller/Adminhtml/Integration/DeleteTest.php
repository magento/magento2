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
use Magento\Integration\Model\Integration as IntegrationModel;

class DeleteTest extends \Magento\Integration\Controller\Adminhtml\IntegrationTest
{
    public function testDeleteAction()
    {
        $intData = $this->_getSampleIntegrationData();
        $this->_requestMock->expects(
            $this->once()
        )->method(
                'getParam'
            )->will(
                $this->returnValue(self::INTEGRATION_ID)
            );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'delete'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        // Use real translate model
        $this->_translateModelMock = null;
        // verify success message
        $this->_messageManager->expects(
            $this->once()
        )->method(
                'addSuccess'
            )->with(
                __('The integration \'%1\' has been deleted.', $intData[Info::DATA_NAME])
            );
        $integrationContr = $this->_createIntegrationController('Delete');
        $integrationContr->execute();
    }

    public function testDeleteActionWithConsumer()
    {
        $intData = $this->_getSampleIntegrationData();
        $intData[Info::DATA_CONSUMER_ID] = 1;
        $this->_requestMock->expects(
            $this->once()
        )->method(
                'getParam'
            )->will(
                $this->returnValue(self::INTEGRATION_ID)
            );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        $this->_integrationSvcMock->expects(
            $this->once()
        )->method(
                'delete'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        $this->_oauthSvcMock->expects(
            $this->once()
        )->method(
                'deleteConsumer'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        // Use real translate model
        $this->_translateModelMock = null;
        // verify success message
        $this->_messageManager->expects(
            $this->once()
        )->method(
                'addSuccess'
            )->with(
                __('The integration \'%1\' has been deleted.', $intData[Info::DATA_NAME])
            );
        $integrationContr = $this->_createIntegrationController('Delete');
        $integrationContr->execute();
    }

    public function testDeleteActionConfigSetUp()
    {
        $intData = $this->_getSampleIntegrationData();
        $intData[Info::DATA_SETUP_TYPE] = IntegrationModel::TYPE_CONFIG;
        $this->_requestMock->expects(
            $this->once()
        )->method(
                'getParam'
            )->will(
                $this->returnValue(self::INTEGRATION_ID)
            );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        $this->_integrationHelperMock->expects(
            $this->once()
        )->method(
                'isConfigType'
            )->with(
                $intData
            )->will(
                $this->returnValue(true)
            );
        // verify error message
        $this->_messageManager->expects(
            $this->once()
        )->method(
                'addError'
            )->with(
                __('Uninstall the extension to remove integration \'%1\'.', $intData[Info::DATA_NAME])
            );
        $this->_integrationSvcMock->expects($this->never())->method('delete');
        // Use real translate model
        $this->_translateModelMock = null;
        // verify success message
        $this->_messageManager->expects($this->never())->method('addSuccess');
        $integrationContr = $this->_createIntegrationController('Delete');
        $integrationContr->execute();
    }

    public function testDeleteActionMissingId()
    {
        $this->_integrationSvcMock->expects($this->never())->method('get');
        $this->_integrationSvcMock->expects($this->never())->method('delete');
        // Use real translate model
        $this->_translateModelMock = null;
        // verify error message
        $this->_messageManager->expects(
            $this->once()
        )->method(
                'addError'
            )->with(
                __('Integration ID is not specified or is invalid.')
            );
        $integrationContr = $this->_createIntegrationController('Delete');
        $integrationContr->execute();
    }

    public function testDeleteActionForServiceIntegrationException()
    {
        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        $this->_requestMock->expects(
            $this->once()
        )->method(
                'getParam'
            )->will(
                $this->returnValue(self::INTEGRATION_ID)
            );
        // Use real translate model
        $this->_translateModelMock = null;
        $exceptionMessage = __("Integration with ID '%1' doesn't exist.", $intData[Info::DATA_ID]);
        $invalidIdException = new \Magento\Integration\Exception($exceptionMessage);
        $this->_integrationSvcMock->expects(
            $this->once()
        )->method(
                'delete'
            )->will(
                $this->throwException($invalidIdException)
            );
        $this->_messageManager->expects($this->once())->method('addError')->with($exceptionMessage);
        $integrationContr = $this->_createIntegrationController('Delete');
        $integrationContr->execute();
    }

    public function testDeleteActionForServiceGenericException()
    {
        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
                'get'
            )->with(
                $this->anything()
            )->will(
                $this->returnValue($intData)
            );
        $this->_requestMock->expects(
            $this->once()
        )->method(
                'getParam'
            )->will(
                $this->returnValue(self::INTEGRATION_ID)
            );
        // Use real translate model
        $this->_translateModelMock = null;
        $exceptionMessage = __("Integration with ID '%1' doesn't exist.", $intData[Info::DATA_ID]);
        $invalidIdException = new \Exception($exceptionMessage);
        $this->_integrationSvcMock->expects(
            $this->once()
        )->method(
                'delete'
            )->will(
                $this->throwException($invalidIdException)
            );
        //Generic Exception(non-Service) should never add the message in session for user display
        $this->_messageManager->expects($this->never())->method('addError');
        $integrationContr = $this->_createIntegrationController('Delete');
        $integrationContr->execute();
    }
}
