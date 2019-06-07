<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\HashGenerator\HashData;
use Magento\Store\Model\StoreSwitcherInterface;
use \Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Authorization\Model\UserContextInterface;
use \Magento\Framework\App\ActionInterface;

/**
 * Generate one time token and build redirect url
 */
class HashGenerator implements StoreSwitcherInterface
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
     * Builds redirect url with token
     *
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        $customerId = null;
        $encodedUrl = $this->urlHelper->getEncodedUrl($redirectUrl);

        if ($this->currentUser->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $customerId = $this->currentUser->getUserId();
        }

        if ($customerId) {
            // phpcs:ignore
            $urlParts = parse_url($targetUrl);
            $host = $urlParts['host'];
            $scheme = $urlParts['scheme'];
            $key = (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
            $timeStamp = time();
            $fromStoreCode = $fromStore->getCode();
            $data = implode(',', [$customerId, $timeStamp, $fromStoreCode]);
            $signature = hash_hmac('sha256', $data, $key);
            $targetUrl = $scheme . "://" . $host . '/stores/store/switchrequest';
            $targetUrl = $this->urlHelper->addRequestParam(
                $targetUrl,
                ['customer_id' => $customerId]
            );
            $targetUrl = $this->urlHelper->addRequestParam($targetUrl, ['time_stamp' => $timeStamp]);
            $targetUrl = $this->urlHelper->addRequestParam($targetUrl, ['signature' => $signature]);
            $targetUrl = $this->urlHelper->addRequestParam($targetUrl, ['___from_store' => $fromStoreCode]);
            $targetUrl = $this->urlHelper->addRequestParam(
                $targetUrl,
                [ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl]
            );
        }
        return $targetUrl;
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
