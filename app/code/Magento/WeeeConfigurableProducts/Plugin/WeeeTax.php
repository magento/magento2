<?php
namespace Magento\WeeeConfigurableProducts\Plugin;

use Magento\Store\Model\Website;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class Tax makes sure that the FPT of the simple products is used.
 */
class WeeeTax
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Tax constructor.
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Weee\Model\Tax $subject
     * @param callable $proceed
     * @param Product $product
     * @param null|false|\Magento\Quote\Model\Quote\Address $shipping
     * @param null|false|\Magento\Quote\Model\Quote\Address $billing
     * @param Website $website
     * @param bool $calculateTax
     * @param bool $round
     * @return mixed
     */
    public function aroundGetProductWeeeAttributes(
        \Magento\Weee\Model\Tax $subject,
        callable $proceed,
        Product $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $round = true
    ) {
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $simpleId = false;

            /** @var Item $quoteItem */
            $quoteItem = $product->getQuoteItem();
            if ($quoteItem) {
                $itemsCollection = $quoteItem->getQuote()->getItemsCollection();
                foreach ($itemsCollection as $item) {
                    if ($item->getProductType() !== Type::TYPE_SIMPLE) {
                        continue;
                    }
                    if ($item->getParentItemId() == $quoteItem->getId()) {
                        $simpleId = $item->getProductId();
                        break;
                    }
                }
            }

            if ($simpleId) {
                $product = $this->productRepository->getById($simpleId);
            }
        }

        $result = $proceed($product, $shipping, $billing, $website, $calculateTax, $round);
        return $result;
    }
}