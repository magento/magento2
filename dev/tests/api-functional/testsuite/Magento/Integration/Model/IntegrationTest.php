<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Authentication;

class IntegrationTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /** @var  \Magento\Integration\Model\Integration */
    protected $integration;

    public function testConfigBasedIntegrationCreation()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Integration\Model\Integration $integrationModel */
        $integrationModel = $objectManager->get('Magento\Integration\Model\Integration');
        $integrationModel->loadByConsumerId(1);
        $this->assertEquals('test-integration@magento.com', $integrationModel->getEmail());
        $this->assertEquals('http://example.com/endpoint1', $integrationModel->getEndpoint());
        $this->assertEquals('Test Integration1', $integrationModel->getName());
        $this->assertEquals(Integration::TYPE_CONFIG, $integrationModel->getSetupType());

        /** @var $integrationService \Magento\Integration\Service\V1\IntegrationInterface */
        $integrationService = $objectManager->get('Magento\Integration\Service\V1\IntegrationInterface');
        $this->integration = $integrationService->findByName('Test Integration1');
        $this->integration->setStatus(Integration::STATUS_ACTIVE)->save();
    }

    /**
     * Test simple request data
     *
     * @depends testConfigBasedIntegrationCreation
     */
    public function testGetServiceCall()
    {
        $this->_markTestAsRestOnly();
        $version = 'V1';
        $restResourcePath = "/{$version}/testmodule4/";

        $itemId = 1;
        $name = 'Test';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $restResourcePath . $itemId,
                'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_GET,
            ],
        ];
        $item = $this->_webApiCall($serviceInfo, [], null, null, $this->integration);
        $this->assertEquals($itemId, $item['entity_id'], 'id field returned incorrectly');
        $this->assertEquals($name, $item['name'], 'name field returned incorrectly');

        OauthHelper::clearApiAccessCredentials();
    }
}
