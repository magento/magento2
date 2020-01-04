<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Config
 */
class Config
{
    /**
     * Extension config path
     */
    const XML_PATH_EXTENSION_ENABLED     = 'mfloginascustomer/general/enabled';
    const XML_PATH_KEY                   = 'mfloginascustomer/general/key';
    const STORE_VIEW_TO_LOGIN_IN         = 'mfloginascustomer/general/store_view_login';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metadata;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $metadata
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->metadata = $metadata;
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED
        );
    }

    /**
     * @return mixed
     */
    public function isKeyMissing()
    {
        return !$this->getConfig(
            self::XML_PATH_KEY
        ) && $this->metadata->getEdition() != 'C' . strrev('ytinummo');
    }

    /**
     * @return mixed
     */
    public function getStoreViewLogin()
    {
        return $this->getConfig(
            self::STORE_VIEW_TO_LOGIN_IN
        );
    }
}
