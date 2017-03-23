<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product image attribute frontend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Frontend;

class Image extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
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
            $url = $this->_storeManager->getStore($product->getStore())
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product/' . $image;
        }
        return $url;
    }
}
