<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;
use \Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Website extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $storeResolver;
    protected $webSiteModel;

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     */
    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\Store\Model\WebSite $webSiteModel
    )
    {
        $this->storeResolver = $storeResolver;
        $this->webSiteModel = $webSiteModel;
    }

    /**
     * Initialize validator
     *
     * @return $this
     */
    public function init()
    {
        return $this;
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if ($value[AdvancedPricing::COL_TIER_PRICE_WEBSITE] != $this->getAllWebsitesValue() &&
            $value[AdvancedPricing::COL_GROUP_PRICE_WEBSITE] != $this->getAllWebsitesValue()) {
            if ((!empty($value[AdvancedPricing::COL_TIER_PRICE_WEBSITE])
                    && !$this->storeResolver->getWebsiteCodeToId($value[AdvancedPricing::COL_TIER_PRICE_WEBSITE]))
                || ((!empty($value[AdvancedPricing::COL_GROUP_PRICE_WEBSITE]))
                    && !$this->storeResolver->getWebsiteCodeToId($value[AdvancedPricing::COL_GROUP_PRICE_WEBSITE]))
            ) {
                $this->_addMessages([self::ERROR_INVALID_WEBSITE]);
                return false;
            }
        }
        return true;
    }

    /**
     * Get all websites value with currency code
     *
     * @return string
     */
    public function getAllWebsitesValue()
    {
        return AdvancedPricing::VALUE_ALL_WEBSITES . ' ['.$this->webSiteModel->getBaseCurrency()->getCurrencyCode().']';
    }
}