<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Bundle\Model;

use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Prepare bundle product links
 */
class PrepareBundleLinks
{
    /** @var LinkInterfaceFactory */
    private $linkFactory;

    /** @var OptionInterfaceFactory */
    private $optionLinkFactory;

    /** @var ProductExtensionFactory */
    private $extensionAttributesFactory;

    /**
     * @param LinkInterfaceFactory $linkFactory
     * @param OptionInterfaceFactory $optionLinkFactory
     * @param ProductExtensionFactory $extensionAttributesFactory
     */
    public function __construct(
        LinkInterfaceFactory $linkFactory,
        OptionInterfaceFactory $optionLinkFactory,
        ProductExtensionFactory $extensionAttributesFactory
    ) {
        $this->linkFactory = $linkFactory;
        $this->optionLinkFactory = $optionLinkFactory;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * Prepare bundle product links
     *
     * @param ProductInterface $product
     * @param array $bundleOptionsData
     * @param array $bundleSelectionsData
     * @return ProductInterface
     */
    public function execute(
        ProductInterface $product,
        array $bundleOptionsData,
        array $bundleSelectionsData
    ): ProductInterface {
        $product->setBundleOptionsData($bundleOptionsData)
            ->setBundleSelectionsData($bundleSelectionsData);
        $options = [];
        foreach ($product->getBundleOptionsData() as $key => $optionData) {
            $option = $this->optionLinkFactory->create(['data' => $optionData]);
            $option->setSku($product->getSku());
            $option->setOptionId(null);
            $links = [];
            $bundleLinks = $product->getBundleSelectionsData();
            foreach ($bundleLinks[$key] as $linkData) {
                $link = $this->linkFactory->create(['data' => $linkData]);
                $link->setQty($linkData['selection_qty']);
                $priceType = $price = null;
                if ($product->getPriceType() === Price::PRICE_TYPE_FIXED) {
                    $priceType = $linkData['selection_price_type'] ?? null;
                    $price = $linkData['selection_price_value'] ?? null;
                }
                $link->setPriceType($priceType);
                $link->setPrice($price);
                $links[] = $link;
            }
            $option->setProductLinks($links);
            $options[] = $option;
        }
        /** @var ProductExtensionFactory $extensionAttributesFactory */
        $extensionAttributes = $product->getExtensionAttributes() ?? $this->extensionAttributesFactory->create();
        $extensionAttributes->setBundleProductOptions($options);
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }
}
