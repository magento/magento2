<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> upstream/2.2-develop
namespace Magento\Catalog\ViewModel\Product;

use Magento\Catalog\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
<<<<<<< HEAD
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Escaper;
=======
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
>>>>>>> upstream/2.2-develop

/**
 * Product breadcrumbs view model.
 */
class Breadcrumbs extends DataObject implements ArgumentInterface
{
    /**
     * Catalog data.
     *
     * @var Data
     */
    private $catalogData;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
<<<<<<< HEAD
=======
     * @var Json
     */
    private $json;
    /**
>>>>>>> upstream/2.2-develop
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Data $catalogData
     * @param ScopeConfigInterface $scopeConfig
<<<<<<< HEAD
     * @param Json|null $json
     * @param Escaper|null $escaper
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
=======
     * @param Json $json
     * @param Escaper $escaper
>>>>>>> upstream/2.2-develop
     */
    public function __construct(
        Data $catalogData,
        ScopeConfigInterface $scopeConfig,
        Json $json = null,
        Escaper $escaper = null
    ) {
        parent::__construct();

        $this->catalogData = $catalogData;
        $this->scopeConfig = $scopeConfig;
<<<<<<< HEAD
=======
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
>>>>>>> upstream/2.2-develop
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Returns category URL suffix.
     *
     * @return mixed
     */
    public function getCategoryUrlSuffix()
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks if categories path is used for product URLs.
     *
     * @return bool
     */
<<<<<<< HEAD
    public function isCategoryUsedInProductUrl(): bool
=======
    public function isCategoryUsedInProductUrl()
>>>>>>> upstream/2.2-develop
    {
        return $this->scopeConfig->isSetFlag(
            'catalog/seo/product_use_categories',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns product name.
     *
     * @return string
     */
<<<<<<< HEAD
    public function getProductName(): string
=======
    public function getProductName()
>>>>>>> upstream/2.2-develop
    {
        return $this->catalogData->getProduct() !== null
            ? $this->catalogData->getProduct()->getName()
            : '';
    }

    /**
<<<<<<< HEAD
     * Returns breadcrumb json with html escaped names
     *
     * @return string
     */
    public function getJsonConfigurationHtmlEscaped() : string
    {
        return json_encode(
            [
                'breadcrumbs' => [
                    'categoryUrlSuffix' => $this->escaper->escapeHtml($this->getCategoryUrlSuffix()),
                    'userCategoryPathInUrl' => (int)$this->isCategoryUsedInProductUrl(),
                    'product' => $this->escaper->escapeHtml($this->getProductName())
                ]
            ],
            JSON_HEX_TAG
        );
    }

    /**
     * Returns breadcrumb json.
     *
     * @return string
     * @deprecated in favor of new method with name {suffix}Html{postfix}()
     */
    public function getJsonConfiguration()
    {
        return $this->getJsonConfigurationHtmlEscaped();
=======
     * Returns breadcrumb json.
     *
     * @return string
     */
    public function getJsonConfiguration()
    {
        return $this->escaper->escapeHtml($this->json->serialize([
            'breadcrumbs' => [
                'categoryUrlSuffix' => $this->escaper->escapeHtml($this->getCategoryUrlSuffix()),
                'userCategoryPathInUrl' => (int)$this->isCategoryUsedInProductUrl(),
                'product' => $this->getProductName()
            ]
        ]));
>>>>>>> upstream/2.2-develop
    }
}
