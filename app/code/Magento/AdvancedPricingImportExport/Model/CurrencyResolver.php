<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Model;

use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Currency resolver for tier price scope
 */
class CurrencyResolver
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DirectoryData
     */
    private $directoryData;

    /**
     * @var string
     */
    private $defaultBaseCurrency;

    /**
     * Associative array with website code as the key and base currency as the value
     *
     * @var array
     */
    private $websitesBaseCurrency;

    /**
     * @param StoreManagerInterface $storeManager
     * @param DirectoryData $directoryData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        DirectoryData $directoryData
    ) {
        $this->storeManager = $storeManager;
        $this->directoryData = $directoryData;
    }

    /**
     * Get base currency for all websites
     *
     * @return array associative array with website code as the key and base currency as the value
     */
    public function getWebsitesBaseCurrency(): array
    {
        if ($this->websitesBaseCurrency === null) {
            $this->websitesBaseCurrency = [];
            foreach ($this->storeManager->getWebsites() as $website) {
                $this->websitesBaseCurrency[$website->getCode()] = $website->getBaseCurrencyCode();
            }
        }

        return $this->websitesBaseCurrency;
    }

    /**
     * Get default scope base currency
     *
     * @return string
     */
    public function getDefaultBaseCurrency(): string
    {
        if ($this->defaultBaseCurrency === null) {
            $this->defaultBaseCurrency = $this->directoryData->getBaseCurrencyCode();
        }

        return $this->defaultBaseCurrency;
    }
}
