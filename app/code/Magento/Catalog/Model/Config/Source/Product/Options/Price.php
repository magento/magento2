<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Product\Options;

use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * Price types mode source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Price implements ProductPriceOptionsInterface
{
    /**
     * Store manager.
     *
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
     * {@inheritdoc}
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
