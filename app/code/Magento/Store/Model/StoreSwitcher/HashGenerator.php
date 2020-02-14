<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\HashGenerator\HashData;

/**
 * Generate one time token and build redirect url
 */
class HashGenerator
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var UserContextInterface
     */
    private $currentUser;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param UrlHelper $urlHelper
     * @param UserContextInterface $currentUser
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        UrlHelper $urlHelper,
        UserContextInterface $currentUser
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->urlHelper = $urlHelper;
        $this->currentUser = $currentUser;
    }

    /**
     * Generate hash data for customer
     *
     * @param StoreInterface $fromStore
     * @return array
     */
    public function generateHash(StoreInterface $fromStore): array
    {
        $key = (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $timeStamp = time();

        $customerId = null;
        $result = [];

        if ($this->currentUser->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $customerId = $this->currentUser->getUserId();

            $data = implode(
                ',',
                [
                    $customerId,
                    $timeStamp,
                    $fromStore->getCode()
                ]
            );
            $signature = hash_hmac('sha256', $data, $key);

            $result = [
                'customer_id' => $customerId,
                'time_stamp' => $timeStamp,
                'signature' => $signature
            ];
        }

        return $result;
    }

    /**
     * Validates one time token
     *
     * @param string $signature
     * @param HashData $hashData
     * @return bool
     */
    public function validateHash(string $signature, HashData $hashData): bool
    {
        if (!empty($signature) && !empty($hashData)) {
            $timeStamp = $hashData->getTimestamp();
            $fromStoreCode = $hashData->getFromStoreCode();
            $customerId = $hashData->getCustomerId();
            $value = implode(",", [$customerId, $timeStamp, $fromStoreCode]);
            $key = (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);

            if (time() - $timeStamp <= 5 && hash_equals($signature, hash_hmac('sha256', $value, $key))) {
                return true;
            }
        }
        return false;
    }
}
