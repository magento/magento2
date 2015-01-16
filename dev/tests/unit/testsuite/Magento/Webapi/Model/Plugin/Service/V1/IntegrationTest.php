<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin\Service\V1;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Integration\Model\Integration;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * API setup plugin
     *
     * @var \Magento\Webapi\Model\Plugin\Service\V1\Integration
     */
    protected $integrationV1Plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /** @var  AclRetriever */
    protected $aclRetrieverMock;

    /**
     * @var \Magento\Integration\Service\V1\AuthorizationServiceInterface
     */
    protected $integrationAuthServiceMock;

    public function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Integration\Service\V1\Integration', [], [], '', false);
        $this->integrationAuthServiceMock = $this->getMockBuilder(
            'Magento\Integration\Service\V1\AuthorizationServiceInterface'
        )->disableOriginalConstructor()->getMock();
        $this->aclRetrieverMock = $this->getMockBuilder('Magento\Authorization\Model\Acl\AclRetriever')
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationV1Plugin = new \Magento\Webapi\Model\Plugin\Service\V1\Integration(
            $this->integrationAuthServiceMock,
            $this->aclRetrieverMock
        );
    }

    public function testAfterDelete()
    {
        $integrationId = 1;
        $integrationsData = [
            Integration::ID => $integrationId,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::SETUP_TYPE => 1,
        ];

        $this->integrationAuthServiceMock->expects($this->once())
            ->method('removePermissions')
            ->with($integrationId);
        $this->integrationV1Plugin->afterDelete($this->subjectMock, $integrationsData);
    }
}
