<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Custom Option Data provider
 */
class PriceUnitLabel
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve price value unit
     *
     * @param string $priceType
     * @return string
     */
    public function getData(string $priceType): string
    {
        if (ProductPriceOptionsInterface::VALUE_PERCENT == $priceType) {
            return '%';
        }

        return $this->getCurrencySymbol();
    }

    /**
     * Get currency symbol
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getCurrencySymbol(): string
    {
        /** @var Store|StoreInterface $store */
        $store = $this->storeManager->getStore();

        return $store->getBaseCurrency()->getCurrencySymbol();
    }
}
