<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin;

use Magento\Integration\Model\Integration;

class SetupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * API Integration config
     *
     * @var \Magento\Webapi\Model\IntegrationConfig
     */
    protected $integrationConfigMock;

    /**
     * Integration service mock
     *
     * @var \Magento\Integration\Service\V1\IntegrationInterface
     */
    protected $integrationServiceMock;

    /**
     * Authorization service mock
     *
     * @var \Magento\Integration\Service\V1\AuthorizationService
     */
    protected $integrationAuthorizationServiceMock;

    /**
     * API setup plugin
     *
     * @var \Magento\Webapi\Model\Plugin\Setup
     */
    protected $apiSetupPlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    public function setUp()
    {
        $this->integrationConfigMock = $this->getMockBuilder(
            '\Magento\Webapi\Model\IntegrationConfig'
        )->disableOriginalConstructor()->setMethods(
            ['getIntegrations']
        )->getMock();

        $this->integrationServiceMock = $this->getMockBuilder(
            '\Magento\Integration\Service\V1\Integration'
        )->disableOriginalConstructor()->setMethods(
            ['findByName']
        )->getMock();

        $this->integrationAuthorizationServiceMock = $this->getMockBuilder(
            '\Magento\Integration\Service\V1\AuthorizationService'
        )->disableOriginalConstructor()->setMethods(
            ['grantPermissions']
        )->getMock();

        $this->subjectMock = $this->getMock('Magento\Integration\Model\Resource\Setup', [], [], '', false);
        $this->apiSetupPlugin = new \Magento\Webapi\Model\Plugin\Setup(
            $this->integrationConfigMock,
            $this->integrationAuthorizationServiceMock,
            $this->integrationServiceMock
        );
    }

    public function testAfterInitIntegrationProcessingNoIntegrations()
    {
        $this->integrationConfigMock->expects($this->never())->method('getIntegrations');
        $this->integrationServiceMock->expects($this->never())->method('findByName');
        $this->apiSetupPlugin->afterInitIntegrationProcessing($this->subjectMock, []);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterInitIntegrationProcessingSuccess()
    {
        $testIntegration1Resource = [
            'Magento_Customer::manage',
            'Magento_Customer::online',
            'Magento_Sales::create',
            'Magento_SalesRule::quote',
        ];
        $testIntegration2Resource = ['Magento_Catalog::product_read'];
        $this->integrationConfigMock->expects(
            $this->once()
        )->method(
            'getIntegrations'
        )->will(
            $this->returnValue(
                [
                    'TestIntegration1' => ['resources' => $testIntegration1Resource],
                    'TestIntegration2' => ['resources' => $testIntegration2Resource],
                ]
            )
        );
        $firstInegrationId = 1;

        $integrationsData1 = new \Magento\Framework\Object(
            [
                'id' => $firstInegrationId,
                Integration::NAME => 'TestIntegration1',
                Integration::EMAIL => 'test-integration1@magento.com',
                Integration::ENDPOINT => 'http://endpoint.com',
                Integration::SETUP_TYPE => 1,
            ]
        );

        $secondIntegrationId = 2;
        $integrationsData2 = new \Magento\Framework\Object(
            [
                'id' => $secondIntegrationId,
                Integration::NAME => 'TestIntegration2',
                Integration::EMAIL => 'test-integration2@magento.com',
                Integration::SETUP_TYPE => 1,
            ]
        );

        $this->integrationServiceMock->expects(
            $this->at(0)
        )->method(
            'findByName'
        )->with(
            'TestIntegration1'
        )->will(
            $this->returnValue($integrationsData1)
        );

        $this->integrationServiceMock->expects(
            $this->at(1)
        )->method(
            'findByName'
        )->with(
            'TestIntegration2'
        )->will(
            $this->returnValue($integrationsData2)
        );

        $this->apiSetupPlugin->afterInitIntegrationProcessing(
            $this->subjectMock,
            ['TestIntegration1', 'TestIntegration2']
        );
    }
}
