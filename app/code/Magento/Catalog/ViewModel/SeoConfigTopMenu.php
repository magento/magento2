<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ViewModel for SEO configurations in the top navigation menu.
 */
class SeoConfigTopMenu extends DataObject implements ArgumentInterface
{
    private const XML_PATH_PRODUCT_USE_CATEGORIES = 'catalog/seo/product_use_categories';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var JsonHexTag
     */
    private JsonHexTag $jsonSerializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonHexTag $jsonSerializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        JsonHexTag $jsonSerializer
    ) {
        parent::__construct();

        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Checks if categories path is used for product URLs.
     *
     * @return bool
     */
    public function isCategoryUsedInProductUrl(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_USE_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Public getter for the JSON serializer.
     *
     * @return JsonHexTag
     */
    public function getJsonSerializer(): JsonHexTag
    {
        return $this->jsonSerializer;
    }

    /**
     * Returns an array of SEO configuration options for the navigation menu.
     *
     * @return array
     */
    public function getSeoConfigOptions(): array
    {
        return [
            'menu' => [
                'useCategoryPathInUrl' => (int)$this->isCategoryUsedInProductUrl()
            ]
        ];
    }
}
