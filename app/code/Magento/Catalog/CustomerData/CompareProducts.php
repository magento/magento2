<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\CustomerData;

use Magento\Catalog\Helper\Output;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Catalog Product Compare Widget
 */
class CompareProducts implements SectionSourceInterface
{
    /**
     * @var Compare
     */
    private $helper;

    /**
     * @var Url
     */
    private $productUrl;

    /**
     * @var Output
     */
    private $outputHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Compare $helper
     * @param Url $productUrl
     * @param Output $outputHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Compare $helper,
        Url $productUrl,
        Output $outputHelper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->productUrl = $productUrl;
        $this->outputHelper = $outputHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getSectionData()
    {
        $count = $this->helper->getItemCount();

        return [
            'count' => $count,
            'countCaption' => $count == 1 ? __('1 item') : __('%1 items', $count),
            'listUrl' => $this->helper->getListUrl(),
            'items' => $count ? $this->getItems() : [],
        ];
    }

    /**
     * Get the list of compared product items
     *
     * @return array
     * @throws LocalizedException
     */
    protected function getItems()
    {
        $items = [];
        $productsScope = $this->scopeConfig->getValue(
            'catalog/recently_products/scope',
            ScopeInterface::SCOPE_WEBSITE
        );
        /** @var Product $item */
        foreach ($this->helper->getItemCollection() as $item) {
            $items[] = [
                'id' => $item->getId(),
                'product_url' => $this->productUrl->getUrl($item),
                'name' => $this->outputHelper->productAttribute($item, $item->getName(), 'name'),
                'remove_url' => $this->helper->getPostDataRemove($item),
                'productScope' => $productsScope
            ];
        }
        return $items;
    }
}
