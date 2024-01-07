<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;

/**
 * Navigation menu view model.
 */
class TopMenu extends DataObject implements ArgumentInterface
{
    private const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/product_url_suffix';
    private const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';
    private const XML_PATH_PRODUCT_USE_CATEGORIES = 'catalog/seo/product_use_categories';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Escaper $escaper
     * @param JsonHexTag $jsonSerializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper,
        JsonHexTag $jsonSerializer
    ) {
        parent::__construct();

        $this->scopeConfig = $scopeConfig;
        $this->escaper = $escaper;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Returns product URL suffix.
     *
     * @return mixed
     */
    public function getProductUrlSuffix()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns category URL suffix.
     *
     * @return mixed
     */
    public function getCategoryUrlSuffix()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
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
     * @return Json
     */
    public function getJsonSerializer()
    {
        return $this->jsonSerializer;
    }

    /**
     * Returns menu json with html escaped names
     *
     * @return string
     */
    public function getJsonConfigurationHtmlEscaped(): string
    {
        return $this->jsonSerializer->serialize(
            [
                'menu' => [
                    'productUrlSuffix' => $this->escaper->escapeHtml($this->getProductUrlSuffix()),
                    'categoryUrlSuffix' => $this->escaper->escapeHtml($this->getCategoryUrlSuffix()),
                    'useCategoryPathInUrl' => (int)$this->isCategoryUsedInProductUrl()
                ]
            ]
        );
    }
}
