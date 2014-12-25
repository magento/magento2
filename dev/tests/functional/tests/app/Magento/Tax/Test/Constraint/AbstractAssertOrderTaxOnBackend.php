<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;
use Mtf\Fixture\InjectableFixture;

/**
 * Checks that prices displayed excluding tax in order are correct on backend.
 */
abstract class AbstractAssertOrderTaxOnBackend extends AbstractConstraint
{
    /**
     * Order View Page.
     *
     * @var OrderView
     */
    protected $orderView;

    /**
     * Order View Page.
     *
     * @var OrderInvoiceNew
     */
    protected $orderInvoiceNew;

    /**
     * Order View Page.
     *
     * @var OrderCreditMemoNew
     */
    protected $orderCreditMemoNew;

    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that specified prices are actual on order, invoice and refund pages.
     *
     * @param array $prices
     * @param InjectableFixture $product
     * @param OrderIndex $orderIndex ,
     * @param OrderView $orderView
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param OrderCreditMemoNew $orderCreditMemoNew
     * @return void
     */
    public function processAssert(
        array $prices,
        InjectableFixture $product,
        OrderIndex $orderIndex,
        OrderView $orderView,
        OrderInvoiceNew $orderInvoiceNew,
        OrderCreditMemoNew $orderCreditMemoNew
    ) {
        $this->orderView = $orderView;
        $this->orderInvoiceNew = $orderInvoiceNew;
        $this->orderCreditMemoNew = $orderCreditMemoNew;
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->openFirstRow();
        //Check prices on order page
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);
        $prices = $this->preparePrices($prices);
        $message = 'Prices on order view page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);
        $orderView->getPageActions()->invoice();
        //Check prices on invoice creation page
        $actualPrices = [];
        $actualPrices = $this->getInvoiceNewPrices($actualPrices, $product);
        $actualPrices = $this->getInvoiceNewTotals($actualPrices);
        $message = 'Prices on invoice new page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);
        $orderInvoiceNew->getTotalsBlock()->submit();
        //Check prices after invoice on order page
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);
        $message = 'Prices on invoice page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);
        $orderView->getPageActions()->orderCreditMemo();
        //Check prices on credit memo creation page
        $pricesCreditMemo = $this->preparePricesCreditMemo($prices);
        $actualPrices = [];
        $actualPrices = $this->getCreditMemoNewPrices($actualPrices, $product);
        $actualPrices = $this->getCreditMemoNewTotals($actualPrices);
        $message = 'Prices on credit memo new page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($pricesCreditMemo, $actualPrices, $message);
        $orderCreditMemoNew->getFormBlock()->submit();
        //Check prices after refund on order page
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);
        $message = 'Prices on credit memo page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, $actualPrices, $message);
    }

    /**
     * Unset category and product page expected prices.
     *
     * @param array $prices
     * @return array
     */
    protected function preparePrices($prices)
    {
        if (isset($prices['category_price_excl_tax'])) {
            unset($prices['category_price_excl_tax']);
        }
        if (isset($prices['category_price_incl_tax'])) {
            unset($prices['category_price_incl_tax']);
        }
        if (isset($prices['product_view_price_excl_tax'])) {
            unset($prices['product_view_price_excl_tax']);
        }
        if (isset($prices['product_view_price_incl_tax'])) {
            unset($prices['product_view_price_incl_tax']);
        }
        return $prices;
    }

    /**
     * Unset category and product page expected prices.
     *
     * @param array $prices
     * @return array
     */
    protected function preparePricesCreditMemo($prices)
    {
        $prices['shipping_excl_tax'] = null;
        $prices['shipping_incl_tax'] = null;
        return $prices;
    }

    /**
     * Get order product prices.
     *
     * @param InjectableFixture $product
     * @param $actualPrices
     * @return array
     */
    public function getOrderPrices($actualPrices, InjectableFixture $product)
    {
        $viewBlock = $this->orderView->getItemsOrderedBlock();
        $actualPrices['cart_item_price_excl_tax'] = $viewBlock->getItemPriceExclTax($product->getName());
        $actualPrices['cart_item_price_incl_tax'] = $viewBlock->getItemPriceInclTax($product->getName());
        $actualPrices['cart_item_subtotal_excl_tax'] = $viewBlock->getItemSubExclTax($product->getName());
        $actualPrices['cart_item_subtotal_incl_tax'] = $viewBlock->getItemSubInclTax($product->getName());
        return $actualPrices;
    }

    /**
     * Get invoice new product prices.
     *
     * @param InjectableFixture $product
     * @param $actualPrices
     * @return array
     */
    public function getInvoiceNewPrices($actualPrices, InjectableFixture $product)
    {
        $productBlock = $this->orderInvoiceNew->getFormBlock()->getItemsBlock()->getItemProductBlock($product);
        $actualPrices['cart_item_price_excl_tax'] = $productBlock->getItemPriceExclTax();
        $actualPrices['cart_item_price_incl_tax'] = $productBlock->getItemPriceInclTax();
        $actualPrices['cart_item_subtotal_excl_tax'] = $productBlock->getItemSubExclTax();
        $actualPrices['cart_item_subtotal_incl_tax'] = $productBlock->getItemSubInclTax();
        return $actualPrices;
    }

    /**
     * Get Credit Memo new product prices.
     *
     * @param InjectableFixture $product
     * @param $actualPrices
     * @return array
     */
    public function getCreditMemoNewPrices($actualPrices, InjectableFixture $product)
    {
        $productBlock = $this->orderCreditMemoNew->getFormBlock()->getItemsBlock()->getItemProductBlock($product);
        $actualPrices['cart_item_price_excl_tax'] = $productBlock->getItemPriceExclTax();
        $actualPrices['cart_item_price_incl_tax'] = $productBlock->getItemPriceInclTax();
        $actualPrices['cart_item_subtotal_excl_tax'] = $productBlock->getItemSubExclTax();
        $actualPrices['cart_item_subtotal_incl_tax'] = $productBlock->getItemSubInclTax();
        return $actualPrices;
    }

    /**
     * Text of price verification after order creation.
     *
     * @return string
     */
    public function toString()
    {
        return 'Prices on backend after order creation is correct.';
    }
}
