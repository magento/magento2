<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Service\V1;

use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Integration Service Interface
 */
interface IntegrationInterface
{
    /**
     * Create a new Integration
     *
     * @param array $integrationData
     * @return IntegrationModel
     * @throws \Magento\Integration\Exception
     */
    public function create(array $integrationData);

    /**
     * Get the details of a specific Integration.
     *
     * @param int $integrationId
     * @return IntegrationModel
     * @throws \Magento\Integration\Exception
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
     * @throws \Magento\Integration\Exception
     */
    public function update(array $integrationData);

    /**
     * Delete an Integration.
     *
     * @param int $integrationId
     * @return array Integration data
     * @throws \Magento\Integration\Exception If the integration does not exist or cannot be deleted
     */
    public function delete($integrationId);
}
