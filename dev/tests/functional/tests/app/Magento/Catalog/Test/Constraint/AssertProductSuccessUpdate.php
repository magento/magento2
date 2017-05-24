<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Checkout\Test\TestStep\AddProductsToTheCartStep;
use Magento\Checkout\Test\TestStep\FillShippingAddressStep;
use Magento\Checkout\Test\TestStep\FillShippingMethodStep;
use Magento\Checkout\Test\TestStep\PlaceOrderStep;
use Magento\Checkout\Test\TestStep\ProceedToCheckoutStep;
use Magento\Checkout\Test\TestStep\SelectPaymentMethodStep;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Address;

/**
 * Assert that product with qty<0 can be updated.
 */
class AssertProductSuccessUpdate extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const SUCCESS_MESSAGE = 'You saved the product.';

    /**
     * Test step factory.
     *
     * @var \Magento\Mtf\TestStep\TestStepFactory
     */
    private $testStepFactory;

    /**
     * Product page with a grid.
     *
     * @var \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex
     */
    protected $productGrid;

    /**
     * Page to update a product.
     *
     * @var \Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit
     */
    protected $editProductPage;

    /**
     * Assert that product with qty<0 can be updated.
     *
     * @param CatalogProductSimple $product
     * @param Address $shippingAddress
     * @param \Magento\Mtf\TestStep\TestStepFactory $testStepFactory
     * @param \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $productGrid
     * @param \Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit $editProductPage
     * @param array $shipping
     * @param array $payment
     * @param CatalogProductSimple $updatedProduct
     */
    public function processAssert(
        CatalogProductSimple $product,
        Address $shippingAddress,
        \Magento\Mtf\TestStep\TestStepFactory $testStepFactory,
        \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $productGrid,
        \Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit $editProductPage,
        array $shipping,
        array $payment,
        CatalogProductSimple $updatedProduct
    ) {
        $this->testStepFactory = $testStepFactory;
        $this->productGrid = $productGrid;
        $this->editProductPage = $editProductPage;

        // Create new order for guest.
        $this->placeOrder($product, $shippingAddress, $shipping, $payment);

        //Update product.
        $this->updateProduct($product, $updatedProduct);

        $actualMessages = $this->editProductPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertContains(
            self::SUCCESS_MESSAGE,
            $actualMessages,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual:\n" . implode("\n - ", $actualMessages)
        );
    }

    /**
     * Place order.
     *
     * @param CatalogProductSimple $product
     * @param Address $address
     * @param array $shipping
     * @param array $payment
     */
    private function placeOrder(
        CatalogProductSimple $product,
        Address $address,
        array $shipping,
        array $payment
    ) {
        // Add products to cart.
        $this->testStepFactory->create(AddProductsToTheCartStep::class, ['products' => [$product]])->run();
        // Proceed to checkout.
        $this->testStepFactory->create(ProceedToCheckoutStep::class)->run();
        // Fill shipping address.
        $this->testStepFactory->create(FillShippingAddressStep::class, ['shippingAddress' => $address])->run();
        // Select shipping method.
        $this->testStepFactory->create(FillShippingMethodStep::class, ['shipping' => $shipping])->run();
        // Select payment method.
        $this->testStepFactory->create(SelectPaymentMethodStep::class, ['payment' => $payment])->run();
        // Click "Place order" button.
        $result = $this->testStepFactory->create(PlaceOrderStep::class)->run();

        \PHPUnit_Framework_Assert::assertNotEmpty(
            $result['orderId'],
            'Order not placed.'
        );
    }

    /**
     * Update product.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductSimple $updatedProduct
     */
    private function updateProduct(
        CatalogProductSimple $product,
        CatalogProductSimple $updatedProduct
    ) {
        $filter = ['sku' => $product->getSku()];

        $this->productGrid->open();
        $this->productGrid->getProductGrid()->searchAndOpen($filter);
        $this->editProductPage->getProductForm()->fill($updatedProduct);
        $this->editProductPage->getFormPageActions()->save();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assertion that product success save message is present.';
    }
}
