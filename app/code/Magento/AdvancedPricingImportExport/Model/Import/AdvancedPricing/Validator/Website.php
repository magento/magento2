<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

/**
 * Class \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website
 *
 * @since 2.0.0
 */
class Website extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     * @since 2.0.0
     */
    protected $storeResolver;

    /**
     * @var \Magento\Store\Model\Website
     * @since 2.0.0
     */
    protected $websiteModel;

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     * @param \Magento\Store\Model\Website $websiteModel
     * @since 2.0.0
     */
    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\Store\Model\Website $websiteModel
    ) {
        $this->storeResolver = $storeResolver;
        $this->websiteModel = $websiteModel;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * Validate by website type
     *
     * @param array $value
     * @param string $websiteCode
     * @return bool
     * @since 2.0.0
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
     * @param mixed $value
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAllWebsitesValue()
    {
        return AdvancedPricing::VALUE_ALL_WEBSITES . ' ['.$this->websiteModel->getBaseCurrency()->getCurrencyCode().']';
    }
}
