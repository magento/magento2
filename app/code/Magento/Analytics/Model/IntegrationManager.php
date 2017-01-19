<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;
use Magento\Integration\Model\Integration;

/**
 * Class IntegrationManager
 */
class IntegrationManager
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * IntegrationManager constructor
     *
     * @param Config $config
     * @param IntegrationServiceInterface $integrationService
     */
    public function __construct(
        Config $config,
        IntegrationServiceInterface $integrationService
    ) {
        $this->integrationService = $integrationService;
        $this->config = $config;
    }

    /**
     * Creates new integration user for MA
     *
     * @return bool
     */
    public function createIntegration()
    {
        $this->integrationService->create($this->getIntegrationData());
        return true;
    }

    /**
     * Activate predefined integration user
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function activateIntegration()
    {
        $integration = $this->integrationService->findByName(
            $this->config->getConfigDataValue('analytics/integration_name')
        );
        if (!$integration->getId()) {
            throw new NoSuchEntityException(__('Cannot find predefined integration user!'));
        }
        $integrationData = $this->getIntegrationData(Integration::STATUS_ACTIVE);
        $integrationData['integration_id'] = $integration->getId();
        $this->integrationService->update($integrationData);
        return true;
    }

    /**
     * Returns default attributes for MA integration user
     *
     * @param int $status
     * @return array
     */
    private function getIntegrationData($status = Integration::STATUS_INACTIVE)
    {
        $integrationData = [
            'name' => $this->config->getConfigDataValue('analytics/integration_name'),
            'status' => $status,
            'all_resources' => false,
            'resource' => [
                'Magento_Analytics::analytics',
                'Magento_Analytics::analytics_api'
            ],
        ];
        return $integrationData;
    }
}
