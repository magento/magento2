<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Catalog\Model\Pricing\SpecialPriceService;

/**
 * Special price model
 */
class SpecialPrice extends AbstractPrice implements SpecialPriceInterface, BasePriceProviderInterface
{
    /**
     * Price type special
     */
    public const PRICE_CODE = 'special_price';

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var SpecialPriceService
     */
    private SpecialPriceService $specialPriceService;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     * @param SpecialPriceService|null $specialPriceService
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate,
        ?SpecialPriceService $specialPriceService = null
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->localeDate = $localeDate;
        $this->specialPriceService = $specialPriceService ?: ObjectManager::getInstance()
            ->get(SpecialPriceService::class);
    }

    /**
     * Retrieve special price.
     *
     * @return bool|float
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = false;
            $specialPrice = $this->getSpecialPrice();
            if ($specialPrice !== null && $specialPrice !== false && $this->isScopeDateInInterval()) {
                $this->value = (float) $specialPrice;
            }
        }

        return $this->value;
    }

    /**
     * Returns special price
     *
     * @return float
     */
    public function getSpecialPrice()
    {
        $specialPrice = $this->product->getSpecialPrice();
        if ($specialPrice !== null && $specialPrice !== false && !$this->isPercentageDiscount()) {
            $specialPrice = $this->priceCurrency->convertAndRound($specialPrice);
        }
        return $specialPrice;
    }

    /**
     * Returns starting date of the special price
     *
     * @return mixed
     */
    public function getSpecialFromDate()
    {
        return $this->product->getSpecialFromDate();
    }

    /**
     * Returns end date of the special price
     *
     * @return mixed
     */
    public function getSpecialToDate()
    {
        return $this->product->getSpecialToDate();
    }

    /**
     * @inheritdoc
     */
    public function isScopeDateInInterval()
    {
        $dateTo = $this->specialPriceService->execute($this->getSpecialToDate());

        return $this->localeDate->isScopeDateInInterval(
            WebsiteInterface::ADMIN_CODE,
            $this->getSpecialFromDate(),
            $dateTo
        );
    }

    /**
     * @inheritdoc
     */
    public function isPercentageDiscount()
    {
        return false;
    }
}
