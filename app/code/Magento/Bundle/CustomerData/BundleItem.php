<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\CustomerData;

/**
 * Bundle item renderer
 */
class BundleItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * Gift card catalog product configuration
     *
     * @var \Magento\Bundle\Helper\Catalog\Product\Configuration
     */
    protected $bundleConfiguration;

    /**
     * @param \Magento\Catalog\Model\Product\Image\View $productImageView
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\Configuration $configurationHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Bundle\Helper\Catalog\Product\Configuration $bundleConfiguration
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Image\View $productImageView,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\Configuration $configurationHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Bundle\Helper\Catalog\Product\Configuration $bundleConfiguration
    ) {
        parent::__construct($productImageView, $msrpHelper, $urlBuilder, $configurationHelper, $checkoutHelper);
        $this->bundleConfiguration = $bundleConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptionList()
    {
        return $this->bundleConfiguration->getOptions($this->item);
    }
}
