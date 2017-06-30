<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Framework\Exception\IntegrationException;

class DeleteTest extends \Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest
{
    /**
     * @var \Magento\Integration\Controller\Adminhtml\Integration\Delete
     */
    protected $integrationController;

    protected function setUp()
    {
        parent::setUp();

        $this->integrationController = $this->_createIntegrationController('Delete');

        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->any())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);
    }

    public function testDeleteAction()
    {
        $intData = $this->_getSampleIntegrationData();
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(self::INTEGRATION_ID);
        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn($intData);
        $this->_integrationSvcMock->expects($this->any())
            ->method('delete')
            ->with($this->anything())
            ->willReturn($intData);
        // Use real translate model
        $this->_translateModelMock = null;
        // verify success message
        $this->_messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('The integration \'%1\' has been deleted.', $intData[Info::DATA_NAME]));

        $this->integrationController->execute();
    }

    public function testDeleteActionWithConsumer()
    {
        $intData = $this->_getSampleIntegrationData();
        $intData[Info::DATA_CONSUMER_ID] = 1;
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(self::INTEGRATION_ID);
        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn($intData);
        $this->_integrationSvcMock->expects($this->once())
            ->method('delete')
            ->with($this->anything())
            ->willReturn($intData);
        $this->_oauthSvcMock->expects($this->once())
            ->method('deleteConsumer')
            ->with($this->anything())
            ->willReturn($intData);
        // Use real translate model
        $this->_translateModelMock = null;
        // verify success message
        $this->_messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('The integration \'%1\' has been deleted.', $intData[Info::DATA_NAME]));

        $this->integrationController->execute();
    }

    public function testDeleteActionConfigSetUp()
    {
        $intData = $this->_getSampleIntegrationData();
        $intData[Info::DATA_SETUP_TYPE] = IntegrationModel::TYPE_CONFIG;
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(self::INTEGRATION_ID);
        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn($intData);
        $this->_integrationHelperMock->expects($this->once())
            ->method('isConfigType')
            ->with($intData)
            ->willReturn(true);
        // verify error message
        $this->_messageManager->expects($this->once())
            ->method('addError')
            ->with(__('Uninstall the extension to remove integration \'%1\'.', $intData[Info::DATA_NAME]));
        $this->_integrationSvcMock->expects($this->never())->method('delete');
        // Use real translate model
        $this->_translateModelMock = null;
        // verify success message
        $this->_messageManager->expects($this->never())->method('addSuccess');

        $this->integrationController->execute();
    }

    public function testDeleteActionMissingId()
    {
        $this->_integrationSvcMock->expects($this->never())->method('get');
        $this->_integrationSvcMock->expects($this->never())->method('delete');
        // Use real translate model
        $this->_translateModelMock = null;
        // verify error message
        $this->_messageManager->expects($this->once())
            ->method('addError')
            ->with(__('Integration ID is not specified or is invalid.'));

        $this->integrationController->execute();
    }

    public function testDeleteActionForServiceIntegrationException()
    {
        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn($intData);
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(self::INTEGRATION_ID);
        // Use real translate model
        $this->_translateModelMock = null;
        $exceptionMessage = __("Integration with ID '%1' doesn't exist.", $intData[Info::DATA_ID]);
        $invalidIdException = new IntegrationException($exceptionMessage);
        $this->_integrationSvcMock->expects($this->once())
            ->method('delete')
            ->willThrowException($invalidIdException);
        $this->_messageManager->expects($this->once())->method('addError');

        $this->integrationController->execute();
    }

    public function testDeleteActionForServiceGenericException()
    {
        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn($intData);
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(self::INTEGRATION_ID);
        // Use real translate model
        $this->_translateModelMock = null;
        $exceptionMessage = __("Integration with ID '%1' doesn't exist.", $intData[Info::DATA_ID]);
        $invalidIdException = new \Exception($exceptionMessage);
        $this->_integrationSvcMock->expects($this->once())
            ->method('delete')
            ->willThrowException($invalidIdException);
        $this->_messageManager->expects($this->never())->method('addError');

        $this->integrationController->execute();
    }
}
