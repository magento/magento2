<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Observer;

use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Modifies the bundle config for the front end to resemble the tax included price when tax included prices.
 */
class GetPriceConfigurationObserver implements ObserverInterface, ResetAfterRequestInterface
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var array Cache of the current bundle selection items
     */
    private $selectionCache = [];

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->registry = $registry;
        $this->taxData = $taxData;
    }

    /**
     * Modify the bundle config for the front end to resemble the tax included price when tax included prices
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->selectionCache = [];
        if ($this->taxData->displayPriceIncludingTax()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->registry->registry('current_product');
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $priceConfigObj = $observer->getData('configObj');
                try {
                    $priceConfig = $this->recurConfigAndUpdatePrice(
                        $priceConfigObj->getConfig(),
                        'prices'
                    );
                    $priceConfigObj->setConfig($priceConfig);
                } catch (\Exception $e) {
                    return $this;
                }
            }
        }
        return $this;
    }

    /**
     * Recurse through the config array and modify the base price
     *
     * @param array $input
     * @param string $searchKey
     * @return array
     */
    private function recurConfigAndUpdatePrice($input, $searchKey)
    {
        $holder = [];
        if (is_array($input)) {
            foreach ($input as $key => $el) {
                if (is_array($el)) {
                    $holder[$key] =
                        $this->recurConfigAndUpdatePrice($el, $searchKey);
                    if ($key === $searchKey
                        && array_key_exists('optionId', $input)
                        && array_key_exists('basePrice', $holder[$key])
                    ) {
                        $holder = $this->updatePriceForBundle($holder, $key);
                    }
                } else {
                    $holder[$key] = $el;
                }
            }
        }
        return $holder;
    }

    /**
     * Update the base price for bundle product option
     *
     * @param array $holder
     * @param int|string $key
     * @return array
     */
    private function updatePriceForBundle($holder, $key)
    {
        if (array_key_exists($key, $holder)
            && array_key_exists('basePrice', $holder[$key])) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->registry->registry('current_product');
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                if (!isset($this->selectionCache[$product->getId()])) {
                    $typeInstance = $product->getTypeInstance();
                    $typeInstance->setStoreFilter($product->getStoreId(), $product);

                    $selectionCollection = $typeInstance->getSelectionsCollection(
                        $typeInstance->getOptionsIds($product),
                        $product
                    );
                    $this->selectionCache[$product->getId()] = $selectionCollection->getItems();
                }
                $arrSelections = $this->selectionCache[$product->getId()];

                foreach ($arrSelections as $selectionItem) {
                    if ($holder['optionId'] == $selectionItem->getId()) {
                        /** @var \Magento\Framework\Pricing\Amount\Base $baseAmount */
                        $baseAmount = $selectionItem->getPriceInfo()->getPrice(BasePrice::PRICE_CODE)->getAmount();
                        /** @var \Magento\Framework\Pricing\Amount\Base $oldAmount */
                        $oldAmount =
                                $selectionItem->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
                        if ($baseAmount->hasAdjustment('tax')) {
                            $holder[$key]['basePrice']['amount'] =
                                    $baseAmount->getBaseAmount() + $baseAmount->getAdjustmentAmount('tax');
                            $holder[$key]['oldPrice']['amount'] =
                                    $oldAmount->getBaseAmount() + $oldAmount->getAdjustmentAmount('tax');
                        }
                    }
                }
            }
        }
        return $holder;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->selectionCache = [];
    }
}
