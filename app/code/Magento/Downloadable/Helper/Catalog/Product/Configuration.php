<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Downloadable\Helper\Catalog\Product;

/**
 * Helper for fetching properties by product configurational item
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Configuration extends \Magento\Framework\App\Helper\AbstractHelper implements
    \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
{
    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfig = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig
    ) {
        $this->productConfig = $productConfig;
        parent::__construct($context);
    }

    /**
     * Retrieves item links options
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getLinks(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $itemLinks = [];
        $linkIds = $item->getOptionByCode('downloadable_link_ids');
        if ($linkIds) {
            $productLinks = $product->getTypeInstance()->getLinks($product);
            foreach (explode(',', $linkIds->getValue()) as $linkId) {
                if (isset($productLinks[$linkId])) {
                    $itemLinks[] = $productLinks[$linkId];
                }
            }
        }
        return $itemLinks;
    }

    /**
     * Retrieves product links section title
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getLinksTitle($product)
    {
        $title = $product->getLinksTitle();
        if (strlen($title)) {
            return $title;
        }
        return $this->scopeConfig->getValue(\Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieves product options
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $options = $this->productConfig->getOptions($item);

        $links = $this->getLinks($item);
        if ($links) {
            $linksOption = ['label' => $this->getLinksTitle($item->getProduct()), 'value' => []];
            foreach ($links as $link) {
                $linksOption['value'][] = $link->getTitle();
            }
            $options[] = $linksOption;
        }

        return $options;
    }
}
