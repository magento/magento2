<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Msrp\Model\Product\Attribute\Source\Type;
use Magento\Msrp\Pricing\MsrpPriceCalculatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Msrp data helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Msrp\Model\Product\Options
     */
    protected $productOptions;

    /**
     * @var \Magento\Msrp\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var MsrpPriceCalculatorInterface
     */
    private $msrpPriceCalculator;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Msrp\Model\Product\Options $productOptions
     * @param \Magento\Msrp\Model\Msrp $msrp
     * @param \Magento\Msrp\Model\Config $config
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param ProductRepositoryInterface $productRepository
     * @param MsrpPriceCalculatorInterface|null $msrpPriceCalculator
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Msrp\Model\Product\Options $productOptions,
        \Magento\Msrp\Model\Msrp $msrp,
        \Magento\Msrp\Model\Config $config,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ProductRepositoryInterface $productRepository,
        MsrpPriceCalculatorInterface $msrpPriceCalculator = null
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->productOptions = $productOptions;
        $this->msrp = $msrp;
        $this->config = $config;
        $this->priceCurrency = $priceCurrency;
        $this->productRepository = $productRepository;
        $this->msrpPriceCalculator = $msrpPriceCalculator
            ?: ObjectManager::getInstance()->get(MsrpPriceCalculatorInterface::class);
    }

    /**
     * Check if can apply Minimum Advertise price to product in specific visibility
     *
     * @param int|Product $product
     * @param int|null $visibility Check displaying price in concrete place (by default generally)
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canApplyMsrp($product, $visibility = null)
    {
        if (!$this->config->isEnabled()) {
            return false;
        }
        if (is_numeric($product)) {
            $product = $this->productRepository->getById($product, false, $this->storeManager->getStore()->getId());
        }
        $result = $this->msrp->canApplyToProduct($product);
        if ($result && $visibility !== null) {
            $productPriceVisibility = $product->getMsrpDisplayActualPriceType();
            if ($productPriceVisibility == Type\Price::TYPE_USE_CONFIG) {
                $productPriceVisibility = $this->config->getDisplayActualPriceType();
            }
            $result = $productPriceVisibility == $visibility;
        }

        if ($product->getTypeInstance()->isComposite($product) && (!$result || $visibility !== null)) {
            $isEnabledInOptions = $this->productOptions->isEnabled($product, $visibility);
            if ($isEnabledInOptions !== null) {
                $result = $isEnabledInOptions;
            }
        }

        return $result;
    }

    /**
     * Get Msrp message for price
     *
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMsrpPriceMessage($product)
    {
        $message = "";
        if ($this->canApplyMsrp($product, Type::TYPE_IN_CART)) {
            $message = __('To see product price, add this item to your cart. You can always remove it later.');
        } elseif ($this->canApplyMsrp($product, Type::TYPE_BEFORE_ORDER_CONFIRM)) {
            $message = __('See price before order confirmation.');
        }
        return $message;
    }

    /**
     * Check is product need gesture to show price
     *
     * @param int|Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowPriceOnGesture($product)
    {
        return $this->canApplyMsrp($product, Type::TYPE_ON_GESTURE);
    }

    /**
     * Check if we should show MAP proce before order confirmation
     *
     * @param int|Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowBeforeOrderConfirm($product)
    {
        return $this->canApplyMsrp($product, Type::TYPE_BEFORE_ORDER_CONFIRM);
    }

    /**
     * Check if any MAP price is larger than as low as value.
     *
     * @param int|Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isMinimalPriceLessMsrp($product)
    {
        if (is_numeric($product)) {
            $product = $this->productRepository->getById($product, false, $this->storeManager->getStore()->getId());
        }
        $msrp = $this->msrpPriceCalculator->getMsrpPriceValue($product);
        $price = $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
        if ($msrp) {
            $msrp = $this->priceCurrency->convertAndRound($msrp);
        }
        return $msrp > $price->getValue();
    }
}
