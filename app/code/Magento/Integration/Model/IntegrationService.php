<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Api\OauthServiceInterface as IntegrationOauthService;
use Magento\Framework\Exception\IntegrationException;

/**
 * Integration Service.
 *
 * This service is used to interact with integrations.
 */
class IntegrationService implements \Magento\Integration\Api\IntegrationServiceInterface
{
    /**
     * @var IntegrationFactory
     */
    protected $_integrationFactory;

    /**
     * @var IntegrationOauthService
     */
    protected $_oauthService;

    /**
     * Construct and initialize Integration Factory
     *
     * @param IntegrationFactory $integrationFactory
     * @param IntegrationOauthService $oauthService
     */
    public function __construct(IntegrationFactory $integrationFactory, IntegrationOauthService $oauthService)
    {
        $this->_integrationFactory = $integrationFactory;
        $this->_oauthService = $oauthService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $integrationData)
    {
        $this->_checkIntegrationByName($integrationData['name']);
        $integration = $this->_integrationFactory->create()->setData($integrationData);
        $integration->save();
        $consumerName = 'Integration' . $integration->getId();
        $consumer = $this->_oauthService->createConsumer(['name' => $consumerName]);
        $integration->setConsumerId($consumer->getId());
        $integration->save();
        return $integration;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $integrationData)
    {
        $integration = $this->_loadIntegrationById($integrationData['integration_id']);
        //If name has been updated check if it conflicts with an existing integration
        if ($integration->getName() != $integrationData['name']) {
            $this->_checkIntegrationByName($integrationData['name']);
        }
        $integration->addData($integrationData);
        $integration->save();
        return $integration;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($integrationId)
    {
        $integration = $this->_loadIntegrationById($integrationId);
        $data = $integration->getData();
        $integration->delete();
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function get($integrationId)
    {
        $integration = $this->_loadIntegrationById($integrationId);
        $this->_addOauthConsumerData($integration);
        $this->_addOauthTokenData($integration);
        return $integration;
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        $integration = $this->_integrationFactory->create()->load($name, 'name');
        return $integration;
    }

    /**
     * {@inheritdoc}
     */
    public function findByConsumerId($consumerId)
    {
        $integration = $this->_integrationFactory->create()->load($consumerId, 'consumer_id');
        return $integration;
    }

    /**
     * {@inheritdoc}
     */
    public function findActiveIntegrationByConsumerId($consumerId)
    {
        $integration = $this->_integrationFactory->create()->loadActiveIntegrationByConsumerId($consumerId);
        return $integration;
    }

    /**
     * Check if an integration exists by the name
     *
     * @param string $name
     * @return void
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    private function _checkIntegrationByName($name)
    {
        $integration = $this->_integrationFactory->create()->load($name, 'name');
        if ($integration->getId()) {
            throw new IntegrationException(__('Integration with name \'%1\' exists.', $name));
        }
    }

    /**
     * Load integration by id.
     *
     * @param int $integrationId
     * @return IntegrationModel
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    protected function _loadIntegrationById($integrationId)
    {
        $integration = $this->_integrationFactory->create()->load($integrationId);
        if (!$integration->getId()) {
            throw new IntegrationException(__('Integration with ID \'%1\' does not exist.', $integrationId));
        }
        return $integration;
    }

    /**
     * Add oAuth consumer key and secret.
     *
     * @param IntegrationModel $integration
     * @return void
     */
    protected function _addOauthConsumerData(IntegrationModel $integration)
    {
        if ($integration->getId()) {
            $consumer = $this->_oauthService->loadConsumer($integration->getConsumerId());
            $integration->setData('consumer_key', $consumer->getKey());
            $integration->setData('consumer_secret', $consumer->getSecret());
        }
    }

    /**
     * Add oAuth token and token secret.
     *
     * @param IntegrationModel $integration
     * @return void
     */
    protected function _addOauthTokenData(IntegrationModel $integration)
    {
        if ($integration->getId()) {
            $accessToken = $this->_oauthService->getAccessToken($integration->getConsumerId());
            if ($accessToken) {
                $integration->setData('token', $accessToken->getToken());
                $integration->setData('token_secret', $accessToken->getSecret());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectedResources($integrationId)
    {
        $integration = $this->get($integrationId);
        $data = $integration->getData();

        $selectedResourceIds = [];
        if ($data && isset($data['resource']) && is_array($data['resource'])) {
            $selectedResourceIds = $data['resource'];
        }
        return $selectedResourceIds;
    }
}
