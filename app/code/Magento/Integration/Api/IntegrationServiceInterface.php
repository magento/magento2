<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Api;

use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Integration Service Interface
 *
 * @api
 * @since 100.0.2
 */
interface IntegrationServiceInterface
{
    /**
     * Create a new Integration
     *
     * @param array $integrationData
     * @return IntegrationModel
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function create(array $integrationData);

    /**
     * Get the details of a specific Integration.
     *
     * @param int $integrationId
     * @return IntegrationModel
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function get($integrationId);

    /**
     * Find Integration by name.
     *
     * @param string $integrationName
     * @return IntegrationModel
     */
    public function findByName($integrationName);

    /**
     * Get the details of an Integration by consumer_id.
     *
     * @param int $consumerId
     * @return IntegrationModel
     */
    public function findByConsumerId($consumerId);

    /**
     * Get the details of an active Integration by consumer_id.
     *
     * @param int $consumerId
     * @return IntegrationModel
     */
    public function findActiveIntegrationByConsumerId($consumerId);

    /**
     * Update an Integration.
     *
     * @param array $integrationData
     * @return IntegrationModel
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function update(array $integrationData);

    /**
     * Delete an Integration.
     *
     * @param int $integrationId
     * @return array Integration data
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function delete($integrationId);

    /**
     * Return an array of selected resources  for an integration.
     *
     * @param int $integrationId
     * @return array
     */
    public function getSelectedResources($integrationId);
}
