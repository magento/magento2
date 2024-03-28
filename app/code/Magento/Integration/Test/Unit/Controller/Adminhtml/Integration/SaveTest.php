<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Controller\Adminhtml\Integration\Save;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTestCase;
use Magento\Security\Model\AdminSessionsManager;

class SaveTest extends IntegrationTestCase
{
    public function testSaveAction()
    {
        // Use real translate model
        $this->_translateModelMock = null;
        $this->_requestMock->expects($this->any())
            ->method('getPostValue')
            ->willReturn([IntegrationController::PARAM_INTEGRATION_ID => self::INTEGRATION_ID]);
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(self::INTEGRATION_ID);
        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with(self::INTEGRATION_ID)
            ->willReturn($intData);
        $this->_integrationSvcMock->expects($this->any())
            ->method('update')
            ->with($this->anything())
            ->willReturn($intData);
        // verify success message
        $this->_messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('The integration \'%1\' has been saved.', $intData[Info::DATA_NAME]));

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);

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
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    public function testSaveActionException()
    {
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(self::INTEGRATION_ID);

        // Have integration service throw an exception to test exception path
        $exceptionMessage = 'Internal error. Check exception log for details.';
        $this->_integrationSvcMock->expects($this->any())
            ->method('get')
            ->with(self::INTEGRATION_ID)
            ->willThrowException(new LocalizedException(__($exceptionMessage)));
        // Verify error
        $this->_messageManager->expects($this->once())->method('addError')->with($exceptionMessage);
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    public function testSaveActionIntegrationException()
    {
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(self::INTEGRATION_ID);

        // Have integration service throw an exception to test exception path
        $exceptionMessage = 'Internal error. Check exception log for details.';
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            self::INTEGRATION_ID
        )->willThrowException(
            new IntegrationException(__($exceptionMessage))
        );

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        // Verify error
        $this->_messageManager->expects($this->once())->method('addError')->with($exceptionMessage);
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
            'getPostValue'
        )->willReturn(
            $integration->getData()
        );
        $integration->setData('id', self::INTEGRATION_ID);
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $this->anything()
        )->willReturn(
            $integration
        );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            self::INTEGRATION_ID
        )->willReturn(
            null
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

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);
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
            'getPostValue'
        )->willReturn(
            $integration->getData()
        );
        $integration->setData('id', self::INTEGRATION_ID);
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $this->anything()
        )->willThrowException(
            new IntegrationException(__($exceptionMessage))
        );
        $this->_integrationSvcMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            self::INTEGRATION_ID
        )->willReturn(
            null
        );

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        // Use real translate model
        $this->_translateModelMock = null;
        // Verify success message
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($exceptionMessage);
        $integrationController = $this->_createIntegrationController('Save');
        $integrationController->execute();
    }

    public function testSaveActionExceptionOnIntegrationsCreatedFromConfigFile()
    {
        $exceptionMessage = "The integrations created in the config file can't be edited.";
        $intData = new DataObject(
            [
                Info::DATA_NAME => 'nameTest',
                Info::DATA_ID => self::INTEGRATION_ID,
                'id' => self::INTEGRATION_ID,
                Info::DATA_EMAIL => 'test@magento.com',
                Info::DATA_ENDPOINT => 'http://magento.ll/endpoint',
                Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_CONFIG,
            ]
        );

        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(self::INTEGRATION_ID);
        $this->_integrationSvcMock
            ->expects($this->once())
            ->method('get')
            ->with(self::INTEGRATION_ID)
            ->willReturn($intData);

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->willReturnArgument(0);

        // Verify error
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($exceptionMessage);
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    /**
     * @return void
     */
    public function testSaveActionUserLockedException()
    {
        $exceptionMessage = __('Your account is temporarily disabled. Please try again later.');
        $passwordString = '1234567';

        $this->_requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(
                function ($arg1) use ($passwordString) {
                    if ($arg1 == Save::PARAM_INTEGRATION_ID) {
                        return self::INTEGRATION_ID;
                    } elseif ($arg1 == Info::DATA_CONSUMER_PASSWORD) {
                        return $passwordString;
                    }
                }
            );

        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects($this->once())
            ->method('get')
            ->with(self::INTEGRATION_ID)
            ->willReturn($intData);

        $this->_userMock->expects($this->any())
            ->method('performIdentityCheck')
            ->with($passwordString)
            ->willThrowException(new UserLockedException(__($exceptionMessage)));

        $this->_authMock->expects($this->once())
            ->method('logout');

        $this->securityCookieMock->expects($this->once())
            ->method('setLogoutReasonCookie')
            ->with(AdminSessionsManager::LOGOUT_REASON_USER_LOCKED);

        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }

    /**
     * @return void
     */
    public function testSaveActionAuthenticationException()
    {
        $passwordString = '1234567';
        $exceptionMessage =
            __('The password entered for the current user is invalid. Verify the password and try again.');

        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnCallback(
                function ($arg1) use ($passwordString) {
                    if ($arg1 == Save::PARAM_INTEGRATION_ID) {
                        return self::INTEGRATION_ID;
                    } elseif ($arg1 == Info::DATA_CONSUMER_PASSWORD) {
                        return $passwordString;
                    }
                }
            );

        $intData = $this->_getSampleIntegrationData();
        $this->_integrationSvcMock->expects($this->once())
            ->method('get')
            ->with(self::INTEGRATION_ID)
            ->willReturn($intData);

        $this->_userMock->expects($this->any())
            ->method('performIdentityCheck')
            ->with($passwordString)
            ->willThrowException(new AuthenticationException(__($exceptionMessage)));

        // Verify error
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($exceptionMessage);
        $integrationContr = $this->_createIntegrationController('Save');
        $integrationContr->execute();
    }
}
