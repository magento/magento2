<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category\Attribute\Frontend;

/**
 * Category image attribute frontend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Image extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    const IMAGE_PATH_SEGMENT = 'catalog/category/';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Return image url
     *
     * @param \Magento\Framework\DataObject $object
     * @return string|null
     * @since 2.0.0
     */
    public function getUrl($object)
    {
        $url = false;
        if ($image = $object->getData($this->getAttribute()->getAttributeCode())) {
            $url = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . self::IMAGE_PATH_SEGMENT . $image;
        }
        return $url;
    }
}
