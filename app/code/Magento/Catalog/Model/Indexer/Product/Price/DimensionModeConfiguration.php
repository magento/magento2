<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;

class DimensionModeConfiguration
{
    /**#@+
     * Available modes of dimensions for product price indexer
     */
    public const DIMENSION_NONE = 'none';
    public const DIMENSION_WEBSITE = 'website';
    public const DIMENSION_CUSTOMER_GROUP = 'customer_group';
    public const DIMENSION_WEBSITE_AND_CUSTOMER_GROUP = 'website_and_customer_group';
    /**#@-*/

    /**
     * Mapping between dimension mode and dimension provider name
     *
     * @var array
     */
    private $modesMapping = [
        self::DIMENSION_NONE => [
        ],
        self::DIMENSION_WEBSITE => [
            WebsiteDimensionProvider::DIMENSION_NAME
        ],
        self::DIMENSION_CUSTOMER_GROUP => [
            CustomerGroupDimensionProvider::DIMENSION_NAME
        ],
        self::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP => [
            WebsiteDimensionProvider::DIMENSION_NAME,
            CustomerGroupDimensionProvider::DIMENSION_NAME
        ],
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $currentMode;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return dimension modes configuration.
     *
     * @return array
     */
    public function getDimensionModes(): array
    {
        return $this->modesMapping;
    }

    /**
     * Get names of dimensions which used for provided mode.
     *
     * By default return dimensions for current enabled mode
     *
     * @param string|null $mode
     * @return string[]
     * @throws \InvalidArgumentException
     */
    public function getDimensionConfiguration(?string $mode = null): array
    {
        if ($mode && !isset($this->modesMapping[$mode])) {
            throw new \InvalidArgumentException(
                sprintf('Undefined dimension mode "%s".', $mode)
            );
        }
        return $this->modesMapping[$mode ?? $this->getCurrentMode()];
    }

    /**
     * Method to get the current mode for product price
     *
     * @return string
     */
    private function getCurrentMode(): string
    {
        if (null === $this->currentMode) {
            $this->currentMode = $this->scopeConfig->getValue(ModeSwitcherConfiguration::XML_PATH_PRICE_DIMENSIONS_MODE)
                ?: self::DIMENSION_NONE;
        }

        return $this->currentMode;
    }
}
