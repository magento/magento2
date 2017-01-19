<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Model for handling Magento BI token value into config.
 */
class AnalyticsToken
{
    /**
     * Path to value of Magento BI token into config.
     */
    private $tokenPath = 'analytics/general/token';

    /**
     * Reinitable Config Model.
     *
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * Scope config model.
     *
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Service which allows to write values into config.
     *
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param ReinitableConfigInterface $reinitableConfig
     * @param ScopeConfigInterface $config
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ReinitableConfigInterface $reinitableConfig,
        ScopeConfigInterface $config,
        WriterInterface $configWriter
    ) {
        $this->reinitableConfig = $reinitableConfig;
        $this->config = $config;
        $this->configWriter = $configWriter;
    }

    /**
     * Get Magento BI token value.
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->config->getValue($this->tokenPath);
    }

    /**
     * Stores Magento BI token value.
     *
     * @param string $value
     *
     * @return bool
     */
    public function storeToken($value)
    {
        $this->configWriter->save($this->tokenPath, $value);
        $this->reinitableConfig->reinit();

        return true;
    }

    /**
     * Check Magento BI token value exist.
     *
     * @return bool
     */
    public function isTokenExist()
    {
        return (bool)$this->getToken();
    }
}
