<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\Catalog\Ui\Component\Listing\Columns;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ThumbnailPlugin
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param ContextInterface $context
     * @param Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct(
        ContextInterface $context,
        Image $imageHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StoreWebsiteRelationInterface $storeWebsiteRelation
    ) {
        $this->context = $context;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
    }

    /**
     * In a multi-website arrangement, get ready the data source for the product thumbnail image.
     *
     * @param Thumbnail $subject
     * @param callable $proceed
     * @param array $dataSource
     * @return array
     *
     * @throws NoSuchEntityException
     */
    public function aroundPrepareDataSource(
        Thumbnail $subject,
        callable $proceed,
        array $dataSource
    ): array {
        if (count($this->storeManager->getWebsites()) === 1) {
            return $proceed($dataSource);
        }
        $allStoresByWebsitesIds = $this->getAllStoresByWebsiteIds();
        if (isset($dataSource['data']['items'])) {
            $fieldName = $subject->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = new DataObject($item);
                $this->setCurrentStore($product, $fieldName, $allStoresByWebsitesIds);
                $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
                $item[$fieldName . '_src'] = $imageHelper->getUrl();
                $item[$fieldName . '_alt'] = $subject->getAlt($item) ?: $imageHelper->getLabel();
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'catalog/product/edit',
                    ['id' => $product->getEntityId(), 'store' => $this->context->getRequestParam('store')]
                );
                $origImageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail_preview');
                $item[$fieldName . '_orig_src'] = $origImageHelper->getUrl();
            }
        }

        return $dataSource;
    }

    /**
     * Set the current store as per product storeId
     *
     * @param DataObject $product
     * @param String $fieldName
     * @param array $storesInWebsites
     * @throws NoSuchEntityException
     */
    private function setCurrentStore(DataObject $product, string $fieldName, array $storesInWebsites): void
    {
        $productWebsites = $product->getWebsiteIds();
        if ($productWebsites) {
            foreach ($productWebsites as $websiteId) {
                foreach ($storesInWebsites[$websiteId] as $storeId) {
                    $this->storeManager->setCurrentStore($storeId);
                    if ($this->scopeConfig->getValue(
                        "catalog/placeholder/{$fieldName}_placeholder",
                        ScopeInterface::SCOPE_STORE,
                        $storeId
                    )) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Get store ids against the websites
     *
     * @return array
     */
    private function getAllStoresByWebsiteIds(): array
    {
        $soresInWebsites = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $store = $this->storeWebsiteRelation->getStoreByWebsiteId($website->getId());
            $soresInWebsites[$website->getId()] = $store;
        }
        return $soresInWebsites;
    }
}
