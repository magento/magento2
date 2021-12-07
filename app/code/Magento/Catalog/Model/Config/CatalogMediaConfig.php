<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Config for catalog media
 */
class CatalogMediaConfig
{
    private const XML_PATH_CATALOG_MEDIA_URL_FORMAT = 'web/url/catalog_media_url_format';

    public const IMAGE_OPTIMIZATION_PARAMETERS = 'image_optimization_parameters';
    public const HASH = 'hash';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
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
    public function getMediaUrlFormat($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CATALOG_MEDIA_URL_FORMAT,
            $scopeType,
            $scopeCode
        );

        if ($value === null) {
            return self::HASH;
        }

        return (string)$value;
    }
}
