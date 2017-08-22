<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \Magento\Catalog\Model\Product\ScopedTierPriceManagement
 *
 */
class ScopedTierPriceManagement implements ScopedProductTierPriceManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @var PriceModifier
     */
    private $priceModifier;

    /**
     * @var TierPriceManagement
     */
    private $tierPriceManagement;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param PriceModifier $priceModifier
     * @param TierPriceManagement $tierPriceManagement
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        PriceModifier $priceModifier,
        TierPriceManagement $tierPriceManagement
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->priceModifier = $priceModifier;
        $this->config = $config;
        $this->tierPriceManagement = $tierPriceManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function add($sku, ProductTierPriceInterface $tierPrice)
    {
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $product->setTierPrices(
            $this->prepareTierPrices($product->getTierPrices(), $tierPrice)
        );
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Could not save group price'));
        }
        return true;
    }

    /**
     * @param array $tierPrices
     * @param ProductTierPriceInterface $tierPrice
     * @return ProductTierPriceInterface[]|null
     */
    private function prepareTierPrices(array $tierPrices, ProductTierPriceInterface $tierPrice)
    {
        $this->validate($tierPrice);
        $websiteId = $this->getWebsiteId();

        foreach ($tierPrices as $index => $item) {
            $tierPriceWebsite = $tierPrice->getExtensionAttributes()
                ? $tierPrice->getExtensionAttributes()->getWebsiteId()
                : 0;

            if ($item->getCustomerGroupId() == $tierPrice->getCustomerGroupId()
                && $websiteId == $tierPriceWebsite
                && $item->getQty() == $tierPrice->getQty()
            ) {
                unset($tierPrices[$index]);
                break;
            }
        }

        $tierPrices[] = $tierPrice;
        return $tierPrices;
    }

    /**
     * @return int
     */
    private function getWebsiteId()
    {
        $websiteIdentifier = 0;
        $value = $this->config->getValue('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE);
        if ($value != 0) {
            $websiteIdentifier = $this->storeManager->getWebsite()->getId();
        }

        return $websiteIdentifier;
    }

    /**
     * @param ProductTierPriceInterface $tierPrice
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    private function validate(ProductTierPriceInterface $tierPrice)
    {
        $data = ['qty' => $tierPrice->getQty(), 'price' => $tierPrice->getValue()];
        foreach ($data as $value) {
            if (!is_float($value) || $value <= 0) {
                throw new \Magento\Framework\Exception\InputException(__('Please provide valid data'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($sku, ProductTierPriceInterface $tierPrice)
    {
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $this->priceModifier->removeTierPrice(
            $product,
            $tierPrice->getCustomerGroupId(),
            $tierPrice->getQty(),
            $this->getWebsiteId()
        );
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku, $customerGroupId)
    {
        return $this->tierPriceManagement->getList($sku, $customerGroupId);
    }
}
