<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\OrderCreditMemoNew;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Checks that prices displayed excluding tax in order are correct on backend.
 */
abstract class AbstractAssertOrderTaxOnBackend extends AbstractConstraint
{
    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

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
     * Implementation for get order total prices function
     *
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getOrderTotals($actualPrices);

    /**
     * Implementation for get invoice creation page total prices function
     *
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getInvoiceNewTotals($actualPrices);

    /**
     * Implementation for get credit memo creation page total prices function
     *
     * @param array $actualPrices
     * @return array
     */
    abstract protected function getCreditMemoNewTotals($actualPrices);

    /**
     * Assert that specified prices are actual on order, invoice and refund pages.
     *
     * @param array $prices
     * @param InjectableFixture $product
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param OrderCreditMemoNew $orderCreditMemoNew
     * @return void
     */
    public function processAssert(
        array $prices,
        InjectableFixture $product,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderInvoiceNew $orderInvoiceNew,
        OrderCreditMemoNew $orderCreditMemoNew
    ) {
        $this->salesOrderView = $salesOrderView;
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
        \PHPUnit_Framework_Assert::assertEquals($prices, array_filter($actualPrices), $message);
        $salesOrderView->getPageActions()->invoice();
        //Check prices on invoice creation page
        $actualPrices = [];
        $actualPrices = $this->getInvoiceNewPrices($actualPrices, $product);
        $actualPrices = $this->getInvoiceNewTotals($actualPrices);
        $message = 'Prices on invoice new page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, array_filter($actualPrices), $message);
        $orderInvoiceNew->getTotalsBlock()->submit();
        //Check prices after invoice on order page
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);
        $message = 'Prices on invoice page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, array_filter($actualPrices), $message);
        $salesOrderView->getPageActions()->orderCreditMemo();
        //Check prices on credit memo creation page
        $actualPrices = [];
        $actualPrices = $this->getCreditMemoNewPrices($actualPrices, $product);
        $actualPrices = $this->getCreditMemoNewTotals($actualPrices);
        $message = 'Prices on credit memo new page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals(
            array_diff_key($prices, ['shipping_excl_tax' => null, 'shipping_incl_tax' => null]),
            array_filter($actualPrices),
            $message
        );
        $orderCreditMemoNew->getFormBlock()->submit();
        //Check prices after refund on order page
        $actualPrices = [];
        $actualPrices = $this->getOrderPrices($actualPrices, $product);
        $actualPrices = $this->getOrderTotals($actualPrices);
        $message = 'Prices on credit memo page should be equal to defined in dataset.';
        \PHPUnit_Framework_Assert::assertEquals($prices, array_filter($actualPrices), $message);
    }

    /**
     * Unset category and product page expected prices.
     *
     * @param array $prices
     * @return array
     */
    protected function preparePrices($prices)
    {
        $deletePrices = [
            'category_price',
            'category_special_price',
            'category_price_excl_tax',
            'category_price_incl_tax',
            'product_view_price',
            'product_view_special_price',
            'product_view_price_excl_tax',
            'product_view_price_incl_tax'
        ];
        foreach ($deletePrices as $key) {
            if (array_key_exists($key, $prices)) {
                unset($prices[$key]);
            }
        }

        return $prices;
    }

    /**
     * Get order product prices.
     *
     * @param InjectableFixture $product
     * @param array $actualPrices
     * @return array
     */
    public function getOrderPrices($actualPrices, InjectableFixture $product)
    {
        $viewBlock = $this->salesOrderView->getItemsOrderedBlock();
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
     * @param array $actualPrices
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
     * @param array $actualPrices
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
