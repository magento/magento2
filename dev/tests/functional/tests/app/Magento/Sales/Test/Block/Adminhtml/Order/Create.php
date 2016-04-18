<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Adminhtml sales order create block.
 */
class Create extends Block
{
    /**
     * Sales order create items block.
     *
     * @var string
     */
    protected $itemsBlock = '#order-items';

    /**
     * Sales order create search products block.
     *
     * @var string
     */
    protected $gridBlock = '#order-search';

    /**
     * Sales order create billing address block.
     *
     * @var string
     */
    protected $billingAddressBlock = '#order-billing_address';

    /**
     * Sales order create shipping address block.
     *
     * @var string
     */
    protected $shippingAddressBlock = '#order-shipping_address';

    /**
     * Sales order create payment method block.
     *
     * @var string
     */
    protected $billingMethodBlock = '#order-billing_method';

    /**
     * Sales order create shipping method block.
     *
     * @var string
     */
    protected $shippingMethodBlock = '#order-shipping_method';

    /**
     * Sales order create totals block.
     *
     * @var string
     */
    protected $totalsBlock = '#order-totals';

    /**
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Order items grid block.
     *
     * @var string
     */
    protected $orderItemsGrid = '#order-items_grid';

    /**
     * Update items button.
     *
     * @var string
     */
    protected $updateItems = '[onclick="order.itemsUpdate()"]';

    /**
     * 'Add Selected Product(s) to Order' button.
     *
     * @var string
     */
    protected $addSelectedProducts = 'button[onclick="order.productGridAddSelected()"]';

    /**
     * Sales order create account information block.
     *
     * @var string
     */
    protected $accountInformationBlock = '#order-form_account';

    /**
     * Payment and Shipping methods block.
     *
     * @var string
     */
    protected $orderMethodsSelector = '#order-methods';

    /**
     * Page header.
     *
     * @var string
     */
    protected $header = 'header';

    /**
     * Getter for order selected products grid.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items
     */
    public function getItemsBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Items',
            ['element' => $this->_rootElement->find($this->itemsBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get sales order create billing address block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing\Address
     */
    public function getBillingAddressBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing\Address',
            ['element' => $this->_rootElement->find($this->billingAddressBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get sales order create billing address block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping\Address
     */
    protected function getShippingAddressBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping\Address',
            ['element' => $this->_rootElement->find($this->shippingAddressBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get sales order create payment method block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing\Method
     */
    protected function getBillingMethodBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing\Method',
            ['element' => $this->_rootElement->find($this->billingMethodBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get sales order create shipping method block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping\Method
     */
    protected function getShippingMethodBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping\Method',
            ['element' => $this->_rootElement->find($this->shippingMethodBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get sales order create totals block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Totals
     */
    protected function getTotalsBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Totals',
            ['element' => $this->_rootElement->find($this->totalsBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get backend abstract block.
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get sales order create search products block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Search\Grid
     */
    public function getGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Search\Grid',
            ['element' => $this->_rootElement->find($this->gridBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get sales order create account information block.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Form\Account
     */
    public function getAccountBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Form\Account',
            ['element' => $this->_rootElement->find($this->accountInformationBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Wait display order items grid.
     *
     * @return void
     */
    public function waitOrderItemsGrid()
    {
        $this->waitForElementVisible($this->orderItemsGrid);
    }

    /**
     * Update product data in sales.
     *
     * @param array $products
     * @return void
     */
    public function updateProductsData(array $products)
    {
        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items $items */
        $items = $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Create\Items',
            ['element' => $this->_rootElement->find($this->itemsBlock)]
        );
        foreach ($products as $product) {
            $items->getItemProductByName($product->getName())->fillCheckoutData($product->getCheckoutData());
        }
        $this->updateItems();
    }

    /**
     * Update product items.
     *
     * @return void
     */
    public function updateItems()
    {
        $this->_rootElement->find($this->updateItems)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Fill addresses based on present data in customer and order fixtures.
     *
     * @param FixtureInterface $address
     * @param string $saveAddress
     * @param bool $setShippingAddress [optional]
     * @return void
     */
    public function fillAddresses(FixtureInterface $address, $saveAddress = 'No', $setShippingAddress = true)
    {
        $this->getShippingAddressBlock()->uncheckSameAsBillingShippingAddress();
        $this->browser->find($this->header)->hover();
        $this->getBillingAddressBlock()->fill($address);
        $this->getBillingAddressBlock()->saveInAddressBookBillingAddress($saveAddress);
        $this->getTemplateBlock()->waitLoader();
        if ($setShippingAddress) {
            $this->browser->find($this->header)->hover();
            $this->getShippingAddressBlock()->setSameAsBillingShippingAddress();
            $this->getTemplateBlock()->waitLoader();
        }
    }

    /**
     * Select shipping method.
     *
     * @param array $shippingMethod
     * @return void
     */
    public function selectShippingMethod(array $shippingMethod)
    {
        $this->_rootElement->find($this->orderMethodsSelector)->click();
        $this->getShippingMethodBlock()->selectShippingMethod($shippingMethod);
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Select payment method.
     *
     * @param array $paymentCode
     * @return void
     */
    public function selectPaymentMethod(array $paymentCode)
    {
        $this->getTemplateBlock()->waitLoader();
        $this->_rootElement->find($this->orderMethodsSelector)->click();
        $this->getBillingMethodBlock()->selectPaymentMethod($paymentCode);
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Submit order.
     *
     * @return void
     */
    public function submitOrder()
    {
        $this->getTotalsBlock()->submitOrder();
    }

    /**
     * Click "Add Selected Product(s) to Order" button.
     *
     * @return void
     */
    public function addSelectedProductsToOrder()
    {
        $this->_rootElement->find($this->addSelectedProducts)->click();
    }
}
