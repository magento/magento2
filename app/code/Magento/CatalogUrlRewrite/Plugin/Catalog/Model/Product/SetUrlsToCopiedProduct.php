<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\Product\Option\Repository as OptionRepository;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

/**
 * Set urls to copied product.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetUrlsToCopiedProduct
{
    private const ENTITY_TYPE = 'product';
    private const URL_PATTERN = '/(.*)-(\d+)$/';
    private const URL_PATH_ATTRIBUTE = 'url_path';
    private const URL_KEY_ATTRIBUTE = 'url_key';

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConnection
     */
    private $urlRewriteCollectionFactory;

    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @param ProductResource $productResource
     * @param ScopeOverriddenValue $scopeOverriddenValue
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionRepository $optionRepository
     * @param UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     */
    public function __construct(
        ProductResource $productResource,
        ScopeOverriddenValue $scopeOverriddenValue,
        ScopeConfigInterface $scopeConfig,
        OptionRepository $optionRepository,
        UrlRewriteCollectionFactory $urlRewriteCollectionFactory
    ) {
        $this->productResource = $productResource;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->scopeConfig = $scopeConfig;
        $this->urlRewriteCollectionFactory = $urlRewriteCollectionFactory;
        $this->optionRepository = $optionRepository;
    }

    /**
     * Sets default url to duplicated product
     *
     * @param Copier $subject
     * @param Product $duplicate
     * @param Product $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCopy(Copier $subject, Product $product, Product $duplicate): array
    {
        $this->setDefaultUrl($product, $duplicate);
        $product->unsetData('url_key');

        return [$product, $duplicate];
    }

    /**
     * Sets stores urls to duplicated product
     *
     * @param Copier $subject
     * @param Product $duplicate
     * @param Product $product
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCopy(Copier $subject, Product $duplicate, Product $product): Product
    {
        $this->setStoresUrl($product, $duplicate);

        return $duplicate;
    }

    /**
     * Set default URL.
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    private function setDefaultUrl(Product $product, Product $duplicate): void
    {
        $duplicate->setStoreId(Store::DEFAULT_STORE_ID);
        $productId = $product->getId();
        $urlKey = $this->productResource->getAttributeRawValue(
            $productId,
            self::URL_KEY_ATTRIBUTE,
            Store::DEFAULT_STORE_ID
        );
        do {
            $urlKey = $this->modifyUrl($urlKey);
            $duplicate->setUrlKey($urlKey);
        } while ($this->isUrlRewriteExists($urlKey));
        $duplicate->setData(self::URL_PATH_ATTRIBUTE, null);
    }

    /**
     * Set URL for each store.
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     * @throws UrlAlreadyExistsException
     * @throws LocalizedException
     */
    private function setStoresUrl(Product $product, Product $duplicate): void
    {
        $productId = $product->getId();
        $attribute = $this->productResource->getAttribute(self::URL_KEY_ATTRIBUTE);
        $duplicate->setData('save_rewrites_history', false);
        foreach ($duplicate->getStoreIds() as $storeId) {
            $useDefault = !$this->scopeOverriddenValue->containsValue(
                ProductInterface::class,
                $product,
                self::URL_KEY_ATTRIBUTE,
                $storeId
            );
            if ($useDefault) {
                continue;
            }

            $duplicate->setStoreId($storeId);
            $urlKey = $this->productResource->getAttributeRawValue($productId, self::URL_KEY_ATTRIBUTE, $storeId);
            $iteration = 0;

            do {
                if ($iteration === 10) {
                    throw new UrlAlreadyExistsException();
                }

                $urlKey = $this->modifyUrl($urlKey);
                $duplicate->setUrlKey($urlKey);
                $iteration++;
            } while (!$attribute->getEntity()->checkAttributeUniqueValue($attribute, $duplicate));
            $duplicate->setData(self::URL_PATH_ATTRIBUTE, null);
            $this->productResource->saveAttribute($duplicate, self::URL_PATH_ATTRIBUTE);
            $this->productResource->saveAttribute($duplicate, self::URL_KEY_ATTRIBUTE);
        }
    }

    /**
     * Modify URL key.
     *
     * @param string $urlKey
     * @return string
     */
    private function modifyUrl(string $urlKey): string
    {
        return preg_match(self::URL_PATTERN, $urlKey, $matches)
            ? $matches[1] . '-' . ($matches[2] + 1)
            : $urlKey . '-1';
    }

    /**
     * Verify if generated url rewrite exists in url_rewrite table
     *
     * @param string $urlKey
     * @return bool
     */
    private function isUrlRewriteExists(string $urlKey): bool
    {
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $urlRewriteCollection->addFieldToFilter(UrlRewrite::ENTITY_TYPE, self::ENTITY_TYPE)
            ->addFieldToFilter(UrlRewrite::REQUEST_PATH, $urlKey . $this->getUrlSuffix());

        return $urlRewriteCollection->getSize() !== 0;
    }

    /**
     * Returns default product url suffix config
     *
     * @return string|null
     */
    private function getUrlSuffix(): ?string
    {
        return $this->scopeConfig->getValue(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX);
    }
}
