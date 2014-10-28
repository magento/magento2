<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Catalog\Service\V1\Data\Product;

class GroupPriceService implements GroupPriceServiceInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Service\V1\Data\Product\GroupPriceBuilder
     */
    protected $groupPriceBuilder;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $customerGroupService;

    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier
     */
    protected $priceModifier;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @param ProductRepository $productRepository
     * @param Product\GroupPriceBuilder $groupPriceBuilder
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $customerGroupService
     * @param \Magento\Catalog\Model\Product\PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        ProductRepository $productRepository,
        Product\GroupPriceBuilder $groupPriceBuilder,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $customerGroupService,
        \Magento\Catalog\Model\Product\PriceModifier $priceModifier,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->productRepository = $productRepository;
        $this->groupPriceBuilder = $groupPriceBuilder;
        $this->storeManager = $storeManager;
        $this->customerGroupService = $customerGroupService;
        $this->priceModifier = $priceModifier;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function set($productSku, \Magento\Catalog\Service\V1\Data\Product\GroupPrice $price)
    {
        $customerGroup = $this->customerGroupService->getGroup($price->getCustomerGroupId());
        $product = $this->productRepository->get($productSku, true);

        $groupPrices = $product->getData('group_price');
        $websiteId = 0;
        if ($this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) != 0) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }
        $found = false;
        foreach ($groupPrices as &$currentPrice) {
            if (intval($currentPrice['cust_group']) === $price->getCustomerGroupId()
                && intval($currentPrice['website_id']) === intval($websiteId)
            ) {
                $currentPrice['price'] = $price->getValue();
                $found = true;
                break;
            }
        }
        if (!$found) {
            $groupPrices[] = array(
                'cust_group' => $customerGroup->getId(),
                'website_id' => $websiteId,
                'price' => $price->getValue(),
            );
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
            $product->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save group price');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($productSku, $customerGroupId)
    {
        $product = $this->productRepository->get($productSku, true);
        if ($this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) == 0) {
            $websiteId = 0;
        } else {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }
        $this->priceModifier->removeGroupPrice($product, $customerGroupId, $websiteId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku)
    {
        $product = $this->productRepository->get($productSku, true);
        $priceKey = 'website_price';
        if ($this->config->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE) == 0) {
            $priceKey = 'price';
        }

        $prices = array();
        foreach ($product->getData('group_price') as $price) {
            $this->groupPriceBuilder->populateWithArray(array(
                Product\GroupPrice::CUSTOMER_GROUP_ID => $price['all_groups'] ? 'all' : $price['cust_group'],
                Product\GroupPrice::VALUE => $price[$priceKey],
            ));
            $prices[] = $this->groupPriceBuilder->create();
        }
        return $prices;
    }
}
