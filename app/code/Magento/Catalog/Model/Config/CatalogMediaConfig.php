<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Config for catalog media
 */
class CatalogMediaConfig
{
    private const XML_PATH_CATALOG_MEDIA_URL_FORMAT = 'web/url/catalog_media_url_format';

    const IMAGE_OPTIMIZATION_PARAMETERS = 'image_optimization_parameters';
    const HASH = 'hash';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get media URL format for catalog images
     *
     * @param string $scopeType
     * @param null|int|string $scopeCode
     * @return string
     */
    public function getMediaUrlFormat($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            CatalogMediaConfig::XML_PATH_CATALOG_MEDIA_URL_FORMAT,
            $scopeType,
            $scopeCode
        );
    }
}
