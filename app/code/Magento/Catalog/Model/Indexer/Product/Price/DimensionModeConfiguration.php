<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;

class DimensionModeConfiguration
{
    /**#@+
     * Available modes of dimensions for product price indexer
     */
    const DIMENSION_NONE = 'none';
    const DIMENSION_WEBSITE = 'website';
    const DIMENSION_CUSTOMER_GROUP = 'customer_group';
    const DIMENSION_WEBSITE_AND_CUSTOMER_GROUP = 'website_and_customer_group';
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
            WebsiteDataProvider::DIMENSION_NAME
        ],
        self::DIMENSION_CUSTOMER_GROUP => [
            CustomerGroupDataProvider::DIMENSION_NAME
        ],
        self::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP => [
            WebsiteDataProvider::DIMENSION_NAME,
            CustomerGroupDataProvider::DIMENSION_NAME
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
     * Get names of dimensions which used for provided mode.
     * By default return dimensions for current enabled mode
     *
     * @param string|null $mode
     * @return string[]
     */
    public function getDimensionConfiguration(string $mode = null): array
    {
        if ($mode && !isset($this->modesMapping[$mode])) {
            throw new \InvalidArgumentException(
                sprintf('Undefined dimension mode "%s".', $mode)
            );
        }
        return $this->modesMapping[$mode ?? $this->getCurrentMode()];
    }

    /**
     * @return string
     */
    private function getCurrentMode(): string
    {
        if (null === $this->currentMode) {
            $this->currentMode = $this->scopeConfig->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE)
                ?: self::DIMENSION_NONE;
        }

        return $this->currentMode;
    }
}
