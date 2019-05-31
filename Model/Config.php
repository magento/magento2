<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Magefan\LoginAsCustomer\Model
 */
class Config
{
    /**
     * Extension config path
     */
    const XML_PATH_EXTENSION_ENABLED     = 'mfloginascustomer/general/enabled';
    const XML_PATH_KEY                   = 'mfloginascustomer/general/key';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
     * @param null $storeId
     * @return mixed
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isKeyMissing($storeId = null)
    {
        return $this->getConfig(
            self::XML_PATH_KEY,
            $storeId
        );
    }
}
