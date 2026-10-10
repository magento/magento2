<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Config\Source\Product\Options;

use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * Price types mode source
 */
class Price implements ProductPriceOptionsInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VALUE_FIXED, 'label' => __('Fixed')],
            ['value' => self::VALUE_PERCENT, 'label' => __('Percent')],
        ];
    }

    /**
     * Get option array of prefixes.
     *
     * @return array
     */
    public function prefixesToOptionArray()
    {
        return [
            ['value' => self::VALUE_FIXED, 'label' => $this->getCurrencySymbol()],
            ['value' => self::VALUE_PERCENT, 'label' => '%'],
        ];
    }

    /**
     * Get currency symbol.
     *
     * @return string
     */
    private function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }
}
