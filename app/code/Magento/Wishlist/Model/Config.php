<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

class Config
{
    const XML_PATH_SHARING_EMAIL_LIMIT = 'wishlist/email/number_limit';

    const XML_PATH_SHARING_TEXT_LIMIT = 'wishlist/email/text_limit';

    const SHARING_EMAIL_LIMIT = 10;

    const SHARING_TEXT_LIMIT = 255;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $_catalogConfig;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    private $_attributeConfig;

    /**
     * Number of emails allowed for sharing wishlist
     *
     * @var int
     */
    private $_sharingEmailLimit;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Attribute\Config $attributeConfig
    ) {
        $emailLimitInConfig = (int)$scopeConfig->getValue(
            self::XML_PATH_SHARING_EMAIL_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $textLimitInConfig = (int)$scopeConfig->getValue(
            self::XML_PATH_SHARING_TEXT_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->_sharingEmailLimit = $emailLimitInConfig ?: self::SHARING_EMAIL_LIMIT;
        $this->_sharignTextLimit = $textLimitInConfig ?: self::SHARING_TEXT_LIMIT;
        $this->_catalogConfig = $catalogConfig;
        $this->_attributeConfig = $attributeConfig;
    }

    /**
     * Get product attributes that need in wishlist
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $catalogAttributes = $this->_catalogConfig->getProductAttributes();
        $wishlistAttributes = $this->_attributeConfig->getAttributeNames('wishlist_item');
        return array_merge($catalogAttributes, $wishlistAttributes);
    }

    /**
     * Retrieve number of emails allowed for sharing wishlist
     *
     * @return int
     */
    public function getSharingEmailLimit()
    {
        return $this->_sharingEmailLimit;
    }

    /**
     * Retrieve maximum length of sharing email text
     *
     * @return int
     */
    public function getSharingTextLimit()
    {
        return $this->_sharignTextLimit;
    }
}
