<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Product;

use Magento\Msrp\Model\Product\Attribute\Source\Type\Price as TypePrice;

/**
 * Class \Magento\Msrp\Model\Product\Options
 *
 * @since 2.0.0
 */
class Options
{
    /**
     * @var \Magento\Msrp\Model\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Msrp\Helper\Data
     * @since 2.0.0
     */
    protected $msrpData;

    /**
     * @param \Magento\Msrp\Model\Config $config
     * @param \Magento\Msrp\Helper\Data $msrpData
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Msrp\Model\Config $config,
        \Magento\Msrp\Helper\Data $msrpData
    ) {
        $this->config = $config;
        $this->msrpData = $msrpData;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param null $visibility
     * @return bool|null
     * @api
     * @since 2.0.0
     */
    public function isEnabled($product, $visibility = null)
    {
        $visibilities = $this->getVisibilities($product);

        $result = (bool)$visibilities ? true : null;
        if ($result && $visibility !== null) {
            if ($visibilities) {
                $maxVisibility = max($visibilities);
                $result = $result && $maxVisibility == $visibility;
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     * @since 2.0.0
     */
    protected function getVisibilities($product)
    {
        /** @var \Magento\Catalog\Model\Product[] $collection */
        $collection = $product->getTypeInstance()->getAssociatedProducts($product) ?: [];
        $visibilities = [];
        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($collection as $item) {
            if ($this->msrpData->canApplyMsrp($item)) {
                $visibilities[] = $item->getMsrpDisplayActualPriceType() == TypePrice::TYPE_USE_CONFIG
                    ? $this->config->getDisplayActualPriceType()
                    : $item->getMsrpDisplayActualPriceType();
            }
        }
        return $visibilities;
    }
}
