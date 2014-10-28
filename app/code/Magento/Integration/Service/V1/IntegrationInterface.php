<?php
/**
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
