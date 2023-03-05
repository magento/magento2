<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model;

use Magento\Catalog\Model\Attribute\Config as AttributeConfig;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @api
 * @since 100.0.2
 */
class Config
{
    const XML_PATH_SHARING_EMAIL_LIMIT = 'wishlist/email/number_limit';

    const XML_PATH_SHARING_TEXT_LIMIT = 'wishlist/email/text_limit';

    const SHARING_EMAIL_LIMIT = 10;

    const SHARING_TEXT_LIMIT = 255;

    /**
     * Number of emails allowed for sharing wishlist
     *
     * @var int
     */
    private $sharingEmailLimit;

    /**
     * @var int
     */
    private $sharignTextLimit;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CatalogConfig $catalogConfig
     * @param AttributeConfig $attributeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        private readonly CatalogConfig $catalogConfig,
        private readonly AttributeConfig $attributeConfig
    ) {
        $emailLimitInConfig = (int)$scopeConfig->getValue(
            self::XML_PATH_SHARING_EMAIL_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
        $textLimitInConfig = (int)$scopeConfig->getValue(
            self::XML_PATH_SHARING_TEXT_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
        $this->sharingEmailLimit = $emailLimitInConfig ?: self::SHARING_EMAIL_LIMIT;
        $this->sharignTextLimit = $textLimitInConfig ?: self::SHARING_TEXT_LIMIT;
    }

    /**
     * Get product attributes that need in wishlist
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $catalogAttributes = $this->catalogConfig->getProductAttributes();
        $wishlistAttributes = $this->attributeConfig->getAttributeNames('wishlist_item');
        return array_merge($catalogAttributes, $wishlistAttributes);
    }

    /**
     * Retrieve number of emails allowed for sharing wishlist
     *
     * @return int
     */
    public function getSharingEmailLimit()
    {
        return $this->sharingEmailLimit;
    }

    /**
     * Retrieve maximum length of sharing email text
     *
     * @return int
     */
    public function getSharingTextLimit()
    {
        return $this->sharignTextLimit;
    }
}
