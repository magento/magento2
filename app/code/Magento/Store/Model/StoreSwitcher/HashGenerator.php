<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Generate one time token and build redirect url
 */
class HashGenerator implements StoreSwitcherInterface
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @param CustomerSession $customerSession
     * @param DeploymentConfig $deploymentConfig
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        CustomerSession $customerSession,
        DeploymentConfig $deploymentConfig,
        UrlHelper $urlHelper
    ) {
        $this->customerSession = $customerSession;
        $this->deploymentConfig = $deploymentConfig;
        $this->urlHelper=$urlHelper;
    }

    /**
     * Builds redirect url with token
     *
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        $customerId = $this->customerSession->getId();

        if ($customerId) {
            $urlParts = parse_url($targetUrl);
            $host = $urlParts['host'];
            $scheme = $urlParts['scheme'];
            $key = (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
            $timeStamp = time();
            $fromStoreCode = $fromStore->getCode();
            $targetStoreCode=$targetStore->getCode();
            $data = implode(',', [$customerId, $timeStamp, $fromStoreCode]);
            $signature = hash_hmac('sha256', $data, $key);
            $targetUrl = $scheme . "://" . $host.'/stores/store/switchrequest';
            $targetUrl = $this->urlHelper->addRequestParam(
                $targetUrl,
                ['customer_id' => $this->customerSession->getId()]
            );
            $targetUrl = $this->urlHelper->addRequestParam($targetUrl, ['time_stamp' => time()]);
            $targetUrl = $this->urlHelper->addRequestParam($targetUrl, ['signature' => $signature]);
            $targetUrl = $this->urlHelper->addRequestParam($targetUrl, ['___from_store' => $fromStoreCode]);
            $targetUrl = $this->urlHelper->addRequestParam($targetUrl, ['___to_store' => $targetStoreCode]);
        }
        return $targetUrl;
    }
}
