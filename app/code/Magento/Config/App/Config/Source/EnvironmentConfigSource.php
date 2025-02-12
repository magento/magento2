<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class for retrieving configurations from environment variables.
 *
 * @api
 * @since 101.0.0
 */
class EnvironmentConfigSource implements ConfigSourceInterface
{
    /**
     * Library for working with arrays.
     *
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * Object for working with placeholders for environment variables.
     *
     * @var PlaceholderInterface
     */
    private $placeholder;

    /**
     * cache for loadConfig()
     *
     * @var array|null
     */
    private $loadConfigCache;

    /**
     * cache for loadConfig()
     *
     * @var string|null
     */
    private $loadConfigCacheEnv;

    /**
     * @param ArrayManager $arrayManager
     * @param PlaceholderFactory $placeholderFactory
     */
    public function __construct(
        ArrayManager $arrayManager,
        PlaceholderFactory $placeholderFactory
    ) {
        $this->arrayManager = $arrayManager;
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function get($path = '')
    {
        $data = new DataObject($this->loadConfig());
        return $data->getData($path) ?: [];
    }

    /**
     * Loads config from environment variables.
     * Caching the result for when this method is called multiple times.
     * The environment variables don't change in run time,  so it is safe to cache.
     *
     * @return array
     */
    private function loadConfig()
    {
        $config = [];
        // phpcs:disable Magento2.Security.Superglobal
        $environmentVariables = $_ENV;
        // phpcs:enable
        if (null !== $this->loadConfigCache && $this->loadConfigCacheEnv === $environmentVariables) {
            return $this->loadConfigCache;
        }
        foreach ($environmentVariables as $template => $value) {
            if (!$this->placeholder->isApplicable($template)) {
                continue;
            }
            $config = $this->arrayManager->set(
                $this->placeholder->restore($template),
                $config,
                $value
            );
        }
        $this->loadConfigCache = $config;
        $this->loadConfigCacheEnv = $environmentVariables;
        return $config;
    }
}
