<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceManagement implements \Magento\Catalog\Api\ProductTierPriceManagementInterface
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
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\PriceModifier $priceModifier,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        GroupManagementInterface $groupManagement,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->productRepository = $productRepository;
        $this->priceFactory = $priceFactory;
        $this->storeManager = $storeManager;
        $this->priceModifier = $priceModifier;
        $this->config = $config;
        $this->groupManagement = $groupManagement;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function add($sku, $customerGroupId, $price, $qty)
    {
        if (!\Zend_Validate::is($price, 'Float') || $price <= 0 || !\Zend_Validate::is($qty, 'Float') || $qty <= 0) {
            throw new InputException(__('Please provide valid data'));
        }
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $tierPrices = $product->getData('tier_price');
        $websiteIdentifier = 0;
        $value = $this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        if ($value != 0) {
            $websiteIdentifier = $this->storeManager->getWebsite()->getId();
        }
        $found = false;

        foreach ($tierPrices as &$item) {
            if ('all' == $customerGroupId) {
                $isGroupValid = ($item['all_groups'] == 1);
            } else {
                $isGroupValid = ($item['cust_group'] == $customerGroupId);
            }

            if ($isGroupValid && $item['website_id'] == $websiteIdentifier && $item['price_qty'] == $qty) {
                $item['price'] = $price;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $mappedCustomerGroupId = 'all' == $customerGroupId
                ? $this->groupManagement->getAllCustomersGroup()->getId()
                : $this->groupRepository->getById($customerGroupId)->getId();

            $tierPrices[] = [
                'cust_group' => $mappedCustomerGroupId,
                'price' => $price,
                'website_price' => $price,
                'website_id' => $websiteIdentifier,
                'price_qty' => $qty,
            ];
        }

        $product->setData('tier_price', $tierPrices);
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

    /**
     * {@inheritdoc}
     */
    public function remove($sku, $customerGroupId, $qty)
    {
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $websiteIdentifier = 0;
        $value = $this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        if ($value != 0) {
            $websiteIdentifier = $this->storeManager->getWebsite()->getId();
        }
        $this->priceModifier->removeTierPrice($product, $customerGroupId, $qty, $websiteIdentifier);
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
