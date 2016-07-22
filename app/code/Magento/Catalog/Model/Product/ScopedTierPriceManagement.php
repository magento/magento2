<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ScopedTierPriceManagement implements \Magento\Catalog\Api\ScopedProductTierPriceManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory
     */
    protected $priceFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier
     */
    protected $priceModifier;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\PriceModifier $priceModifier,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        GroupManagementInterface $groupManagement
    ) {
        $this->productRepository = $productRepository;
        $this->priceFactory = $priceFactory;
        $this->storeManager = $storeManager;
        $this->priceModifier = $priceModifier;
        $this->config = $config;
        $this->groupManagement = $groupManagement;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function add($sku, \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice)
    {
        $this->validatePrice($tierPrice);
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $tierPrices = $product->getTierPrices();
        $websiteIdentifier = 0;
        $value = $this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        if ($value != 0) {
            $websiteIdentifier = $this->storeManager->getWebsite()->getId();
        }
        $found = false;

        foreach ($tierPrices as $item) {
            $tierPriceWebsite = $tierPrice->getExtensionAttributes() ? $tierPrice->getExtensionAttributes()->getWebsiteId() : 0;
            if ($item->getCustomerGroupId() == $tierPrice->getCustomerGroupId()
                    && $websiteIdentifier == $tierPriceWebsite
                    && $item->getQty() == $tierPrice->getQty()) {
                $item->setValue($tierPrice->getValue());
                $item->getExtensionAttributes()
                    ->setPercentageValue($tierPrice->getExtensionAttributes()->getPercentageValue());
                $found = true;
                break;
            }
        }
        if (!$found) {
            $tierPrices[] = $tierPrice;
        }

        $product->setTierPrices($tierPrices);
        $errors = $product->validate();
        if (is_array($errors) && count($errors)) {
            $errorAttributeCodes = implode(', ', array_keys($errors));
            throw new InputException(
                __('Values of following attributes are invalid: %1', $errorAttributeCodes)
            );
        }
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save group price'));
        }
        return true;
    }

    protected function validatePrice(\Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice)
    {
        $data = ['qty' => $tierPrice->getQty()];
        foreach ($data as $value) {
            if (!\Zend_Validate::is($value, 'Float') || $value <= 0) {
                throw new InputException(__('Please provide valid data'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($sku, \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice)
    {
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $websiteIdentifier = 0;
        $value = $this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        if ($value != 0) {
            $websiteIdentifier = $this->storeManager->getWebsite()->getId();
        }
        $this->priceModifier->removeTierPrice(
            $product,
            $tierPrice->getCustomerGroupId(),
            $tierPrice->getQty(),
            $websiteIdentifier)
        ;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku, $customerGroupId)
    {
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);

        $priceKey = 'website_price';
        $value = $this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        if ($value == 0) {
            $priceKey = 'price';
        }

        $cgi = ($customerGroupId === 'all'
            ? $this->groupManagement->getAllCustomersGroup()->getId()
            : $customerGroupId);

        $prices = [];
        foreach ($product->getData('tier_price') as $price) {
            if ((is_numeric($customerGroupId) && intval($price['cust_group']) === intval($customerGroupId))
                || ($customerGroupId === 'all' && $price['all_groups'])
            ) {
                /** @var \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice */
                $tierPrice = $this->priceFactory->create();
                $tierPrice->setValue($price[$priceKey])
                    ->setQty($price['price_qty'])
                    ->setCustomerGroupId($cgi);
                $prices[] = $tierPrice;
            }
        }
        return $prices;
    }
}
