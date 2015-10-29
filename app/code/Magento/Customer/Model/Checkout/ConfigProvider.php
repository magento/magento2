<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Url;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'customerLoginUrl' => $this->getLoginUrl(),
            'isRedirectRequired' => $this->isRedirectRequired(),
        ];
    }

    /**
     * Returns URL to login controller action
     *
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->urlBuilder->getUrl(Url::ROUTE_ACCOUNT_LOGIN);
    }

    /**
     * Whether redirect to login page is required
     *
     * @return bool
     */
    protected function isRedirectRequired()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if (strpos($this->getLoginUrl(), $baseUrl) !== false) {
            return false;
        }

        return true;
    }

}
