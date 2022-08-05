<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\CurrencyResolver;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Website as WebsiteModel;

class Website extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var StoreResolver
     */
    protected $storeResolver;

    /**
     * @var WebsiteModel
     */
    protected $websiteModel;

    /**
     * @var CurrencyResolver
     */
    private $currencyResolver;

    /**
     * @param StoreResolver $storeResolver
     * @param WebsiteModel $websiteModel
     * @param CurrencyResolver|null $currencyResolver
     */
    public function __construct(
        StoreResolver $storeResolver,
        WebsiteModel $websiteModel,
        ?CurrencyResolver $currencyResolver = null
    ) {
        $this->storeResolver = $storeResolver;
        $this->websiteModel = $websiteModel;
        $this->currencyResolver = $currencyResolver ?? ObjectManager::getInstance()->get(CurrencyResolver::class);
    }

    /**
     * Validate by website type
     *
     * @param array $value
     * @param string $websiteCode
     *
     * @return bool
     */
    protected function isWebsiteValid($value, $websiteCode)
    {
        if (isset($value[$websiteCode]) && !empty($value[$websiteCode])) {
            if ($value[$websiteCode] != $this->getAllWebsitesValue()
                && !$this->storeResolver->getWebsiteCodeToId($value[$websiteCode])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate value
     *
     * @param array $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $valid = true;
        if (isset($value[AdvancedPricing::COL_TIER_PRICE]) && !empty($value[AdvancedPricing::COL_TIER_PRICE])) {
            $valid *= $this->isWebsiteValid($value, AdvancedPricing::COL_TIER_PRICE_WEBSITE);
        }
        if (!$valid) {
            $this->_addMessages([self::ERROR_INVALID_WEBSITE]);
        }
        return $valid;
    }

    /**
     * Get all websites value with currency code
     *
     * @return string
     */
    public function getAllWebsitesValue()
    {
        return AdvancedPricing::VALUE_ALL_WEBSITES .
            ' [' . $this->currencyResolver->getDefaultBaseCurrency() . ']';
    }
}
