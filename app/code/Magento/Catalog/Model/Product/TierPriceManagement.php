<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\Validator\FloatUtils;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;

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
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function add($sku, $customerGroupId, $price, $qty)
    {
        if (!is_float($price) && !is_int($price) && !ValidatorChain::is((string)$price, FloatUtils::class)
            || !is_float($qty) && !is_int($qty) && !ValidatorChain::is((string)$qty, FloatUtils::class)
            || $price <= 0
            || $qty <= 0
        ) {
            throw new InputException(__('The data was invalid. Verify the data and try again.'));
        }
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $tierPrices = $product->getData('tier_price');
        $websiteIdentifier = 0;
        $value = $this->config->getValue('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE);
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
                __('Values in the %1 attributes are invalid. Verify the values and try again.', $errorAttributeCodes)
            );
        }
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            if ($e instanceof TemporaryStateExceptionInterface) {
                // temporary state exception must be already localized
                throw $e;
            }
            throw new CouldNotSaveException(__("The group price couldn't be saved."));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($sku, $customerGroupId, $qty)
    {
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);
        $websiteIdentifier = 0;
        if ($this->getPriceScopeConfig() !== 0) {
            $websiteIdentifier = $this->getCurrentWebsite()->getId();
        }
        $this->priceModifier->removeTierPrice($product, $customerGroupId, $qty, $websiteIdentifier);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList($sku, $customerGroupId)
    {
        $product = $this->productRepository->get($sku, ['edit_mode' => true]);

        $cgi = $customerGroupId === 'all'
            ? $this->groupManagement->getAllCustomersGroup()->getId()
            : $customerGroupId;

        $prices = [];
        $tierPrices = $product->getData('tier_price');
        if ($tierPrices !== null) {
            $priceKey = $this->getPriceKey();

            foreach ($tierPrices as $price) {
                if ($this->isCustomerGroupApplicable($customerGroupId, $price)) {
                    /** @var \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice */
                    $tierPrice = $this->priceFactory->create();
                    $tierPrice->setValue($price[$priceKey])
                        ->setQty($price['price_qty'])
                        ->setCustomerGroupId($cgi);
                    $prices[] = $tierPrice;
                }
            }
        }
        return $prices;
    }

    /**
     * Returns attribute code (key) that contains price
     *
     * @return string
     */
    private function getPriceKey(): string
    {
        return $this->getPriceScopeConfig() === 0 ? 'price' : 'website_price';
    }

    /**
     * Returns whether Price is applicable for provided Customer Group
     *
     * @param string $customerGroupId
     * @param array $priceArray
     * @return bool
     */
    private function isCustomerGroupApplicable(string $customerGroupId, array $priceArray): bool
    {
        return ($customerGroupId === 'all' && $priceArray['all_groups'])
            || (is_numeric($customerGroupId) && (int)$priceArray['cust_group'] === (int)$customerGroupId);
    }

    /**
     * Returns current Price Scope configuration value
     *
     * @return int
     */
    private function getPriceScopeConfig(): int
    {
        return (int)$this->config->getValue('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Returns current Website object
     *
     * @return WebsiteInterface
     * @throws LocalizedException
     */
    private function getCurrentWebsite(): WebsiteInterface
    {
        return $this->storeManager->getWebsite();
    }
}
