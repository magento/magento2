<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses;
use Magento\Framework\AuthorizationInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerAssistance\Api\IsAssistanceEnabledInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\GetLoginAsCustomerAssistanceAllowed;

/**
 * Plugin for managing assistance_allowed extension attribute in Customer form Data Provider.
 */
class DataProviderWithDefaultAddressesPlugin
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var GetLoginAsCustomerAssistanceAllowed
     */
    private $getLoginAsCustomerAssistanceAllowed;

    /**
     * @param AuthorizationInterface $authorization
     * @param ConfigInterface $config
     * @param GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ConfigInterface $config,
        GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
        $this->getLoginAsCustomerAssistanceAllowed = $getLoginAsCustomerAssistanceAllowed;
    }

    /**
     * Add assistance_allowed extension attribute data to Customer form Data Provider.
     *
     * @param DataProviderWithDefaultAddresses $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        DataProviderWithDefaultAddresses $subject,
        array $result
    ): array {
        $isAssistanceAllowed = [];

        foreach ($result as $id => $entityData) {
            if ($id) {
                $assistanceAllowedStatus = $this->resolveStatus(
                    $this->getLoginAsCustomerAssistanceAllowed->execute((int)$entityData['customer_id'])
                );
                $isAssistanceAllowed[$id]['customer']['extension_attributes']['assistance_allowed'] =
                    (string)$assistanceAllowedStatus;
            }
        }

        return array_replace_recursive($result, $isAssistanceAllowed);
    }

    /**
     * Modify assistance_allowed extension attribute metadata for Customer form Data Provider.
     *
     * @param DataProviderWithDefaultAddresses $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMeta(
        DataProviderWithDefaultAddresses $subject,
        array $result
    ): array {
        if (!$this->config->isEnabled()) {
            $assistanceAllowedConfig = ['visible' => false];
        } elseif (!$this->authorization->isAllowed('Magento_LoginAsCustomer::allow_shopping_assistance')) {
            $assistanceAllowedConfig = [
                'disabled' => true,
                'notice' => __('You have no permission to change Opt-In preference.'),
            ];
        } else {
            $assistanceAllowedConfig = [];
        }

        $config = [
            'customer' => [
                'children' => [
                    'extension_attributes.assistance_allowed' => [
                        'arguments' => [
                            'data' => [
                                'config' => $assistanceAllowedConfig,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return array_replace_recursive($result, $config);
    }

    /**
     * Get integer status value from boolean.
     *
     * @param bool $assistanceAllowed
     * @return int
     */
    private function resolveStatus(bool $assistanceAllowed): int
    {
        return $assistanceAllowed ? IsAssistanceEnabledInterface::ALLOWED : IsAssistanceEnabledInterface::DENIED;
    }
}
