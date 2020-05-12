<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Authentication\OauthHelper;

class IntegrationTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /** @var  \Magento\Integration\Model\Integration */
    protected $integration;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var $integrationService \Magento\Integration\Api\IntegrationServiceInterface */
        $integrationService = $objectManager->get(\Magento\Integration\Api\IntegrationServiceInterface::class);

        $params = [
            'all_resources' => true,
            'integration_id' => 1,
            'status' => Integration::STATUS_ACTIVE,
            'name' => 'Test Integration1'
        ];
        $this->integration = $integrationService->update($params);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->integration = null;
        OauthHelper::clearApiAccessCredentials();
        parent::tearDown();
    }

    public function testConfigBasedIntegrationCreation()
    {
        $this->assertEquals('test-integration@magento.com', $this->integration->getEmail());
        $this->assertEquals('http://example.com/endpoint1', $this->integration->getEndpoint());
        $this->assertEquals('Test Integration1', $this->integration->getName());
        $this->assertEquals(Integration::TYPE_CONFIG, $this->integration->getSetupType());
    }

    /**
     * Test simple request data
     *
     * @depends testConfigBasedIntegrationCreation
     */
    public function testGetServiceCall()
    {
        $this->_markTestAsRestOnly();
        $itemId = 1;
        $name = 'Test';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/testmodule4/' . $itemId,
                'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_GET,
            ],
        ];
        $item = $this->_webApiCall($serviceInfo, [], null, null, $this->integration);
        $this->assertEquals($itemId, $item['entity_id'], 'id field returned incorrectly');
        $this->assertEquals($name, $item['name'], 'name field returned incorrectly');
    }
}
