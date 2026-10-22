<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product image attribute frontend
 */
class Image extends AbstractFrontend
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Returns url to product image
     *
     * @param  \Magento\Catalog\Model\Product $product
     *
     * @return string|false
     */
    public function getUrl($product)
    {
        $image = $product->getData($this->getAttribute()->getAttributeCode());
        $url = false;
        if (!empty($image)) {
            $url = $this->_storeManager
                    ->getStore($product->getStore())
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/' . ltrim($image, '/');
        }
        return $url;
    }
}
