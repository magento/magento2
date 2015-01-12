<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

class GroupPriceManagement implements \Magento\Catalog\Api\ProductGroupPriceManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductGroupPriceDataBuilder
     */
    protected $groupPriceBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier
     */
    protected $priceModifier;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\Data\ProductGroupPriceDataBuilder $groupPriceBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductGroupPriceDataBuilder $groupPriceBuilder,
        GroupRepositoryInterface $groupRepository,
        \Magento\Catalog\Model\Product\PriceModifier $priceModifier,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->groupPriceBuilder = $groupPriceBuilder;
        $this->groupRepository = $groupRepository;
        $this->priceModifier = $priceModifier;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add($productSku, $customerGroupId, $price)
    {
        if (!\Zend_Validate::is($price, 'Float') || $price <= 0 || !\Zend_Validate::is($price, 'Float')) {
            throw new InputException('Please provide valid data');
        }
        $customerGroup = $this->groupRepository->getById($customerGroupId);
        $product = $this->productRepository->get($productSku, true);
        $groupPrices = $product->getData('group_price');
        $websiteIdentifier = 0;
        if ($this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) != 0) {
            $websiteIdentifier = $this->storeManager->getWebsite()->getId();
        }
        $found = false;
        foreach ($groupPrices as &$currentPrice) {
            if (intval($currentPrice['cust_group']) === $customerGroupId
                && intval($currentPrice['website_id']) === intval($websiteIdentifier)
            ) {
                $currentPrice['price'] = $price;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $groupPrices[] = [
                'cust_group' => $customerGroup->getId(),
                'website_id' => $websiteIdentifier,
                'price' => $price,
            ];
        }

        $product->setData('group_price', $groupPrices);
        $errors = $product->validate();
        if (is_array($errors) && count($errors)) {
            $errorAttributeCodes = implode(', ', array_keys($errors));
            throw new InputException(
                sprintf('Values of following attributes are invalid: %s', $errorAttributeCodes)
            );
        }
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save group price');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($productSku, $customerGroupId)
    {
        $product = $this->productRepository->get($productSku, true);
        $websiteIdentifier = 0;
        if ($this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) != 0) {
            $websiteIdentifier = $this->storeManager->getWebsite()->getId();
        }
        $this->priceModifier->removeGroupPrice($product, $customerGroupId, $websiteIdentifier);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku, $websiteId = null)
    {
        $product = $this->productRepository->get($productSku, true);
        $priceKey = 'website_price';
        if ($this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) == 0) {
            $priceKey = 'price';
        }

        $prices = [];
        foreach ($product->getData('group_price') as $price) {
            $this->groupPriceBuilder->populateWithArray(
                [
                    'customer_group_id' => $price['all_groups'] ? 'all' : $price['cust_group'],
                    'value' => $price[$priceKey],
                ]
            );
            $prices[] = $this->groupPriceBuilder->create();
        }
        return $prices;
    }
}
