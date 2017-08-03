<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Class \Magento\Catalog\CustomerData\CompareProducts
 *
 * @since 2.0.0
 */
class CompareProducts implements SectionSourceInterface
{
    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     * @since 2.0.0
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     * @since 2.0.0
     */
    protected $productUrl;

    /**
     * @param \Magento\Catalog\Helper\Product\Compare $helper
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Compare $helper,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Catalog\Helper\Output $outputHelper
    ) {
        $this->helper = $helper;
        $this->productUrl = $productUrl;
        $this->outputHelper = $outputHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @return array
     * @since 2.0.0
     */
    protected function getItems()
    {
        $items = [];
        foreach ($this->helper->getItemCollection() as $item) {
            $items[] = [
                'id' => $item->getId(),
                'product_url' => $this->productUrl->getUrl($item),
                'name' => $this->outputHelper->productAttribute($item, $item->getName(), 'name'),
                'remove_url' => $this->helper->getPostDataRemove($item),
            ];
        }
        return $items;
    }
}
