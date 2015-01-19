<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Create
 * Adminhtml sales order create block
 */
class Create extends Block
{
    /**
     * Sales order create items block
     *
     * @var string
     */
    protected $itemsBlock = '#order-items';

    /**
     * Sales order create search products block
     *
     * @var string
     */
    protected $gridBlock = '#order-search';

    /**
     * Sales order create billing address block
     *
     * @var string
     */
    protected $billingAddressBlock = '#order-billing_address';

    /**
     * Sales order create shipping address block
     *
     * @var string
     */
    protected $shippingAddressBlock = '#order-shipping_address';

    /**
     * Sales order create payment method block
     *
     * @var string
     */
    protected $billingMethodBlock = '#order-billing_method';

    /**
     * Sales order create shipping method block
     *
     * @var string
     */
    protected $shippingMethodBlock = '#order-shipping_method';

    /**
     * Sales order create totals block
     *
     * @var string
     */
    protected $totalsBlock = '#order-totals';

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Order items grid block
     *
     * @var string
     */
    protected $orderItemsGrid = '#order-items_grid';

    /**
     * Update items button
     *
     * @var string
     */
    protected $updateItems = '#order-items_grid p button';

    /**
     * 'Add Selected Product(s) to Order' button
     *
     * @var string
     */
    protected $addSelectedProducts = 'button[onclick="order.productGridAddSelected()"]';

    /**
     * Getter for order selected products grid
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Items
     */
    public function getItemsBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderCreateItems(
            $this->_rootElement->find($this->itemsBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get sales order create billing address block
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing\Address
     */
    public function getBillingAddressBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderCreateBillingAddress(
            $this->_rootElement->find($this->billingAddressBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get sales order create billing address block
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping\Address
     */
    protected function getShippingAddressBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderCreateShippingAddress(
            $this->_rootElement->find($this->shippingAddressBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get sales order create payment method block
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing\Method
     */
    protected function getBillingMethodBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderCreateBillingMethod(
            $this->_rootElement->find($this->billingMethodBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get sales order create shipping method block
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping\Method
     */
    protected function getShippingMethodBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderCreateShippingMethod(
            $this->_rootElement->find($this->shippingMethodBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get sales order create totals block
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Totals
     */
    protected function getTotalsBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderCreateTotals(
            $this->_rootElement->find($this->totalsBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    public function getTemplateBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Get sales order create search products block
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Create\Search\Grid
     */
    public function getGridBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderCreateSearchGrid(
            $this->_rootElement->find($this->gridBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Wait display order items grid
     *
     * @return void
     */
    public function waitOrderItemsGrid()
    {
        $this->waitForElementVisible($this->orderItemsGrid);
    }

    /**
     * Update product data in sales
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
            $items->getItemProductByName($product->getName())
                ->fill($product->getDataFieldConfig('checkout_data')['source']);
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
     * Fill addresses based on present data in customer and order fixtures
     *
     * @param FixtureInterface $address
     * @return void
     */
    public function fillAddresses(FixtureInterface $address)
    {
        $this->getShippingAddressBlock()->uncheckSameAsBillingShippingAddress();
        $this->getTemplateBlock()->waitLoader();
        $this->getBillingAddressBlock()->fill($address);
        $this->getShippingAddressBlock()->setSameAsBillingShippingAddress();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Select shipping method
     *
     * @param array $shippingMethod
     * @return void
     */
    public function selectShippingMethod(array $shippingMethod)
    {
        $this->getShippingMethodBlock()->selectShippingMethod($shippingMethod);
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Select payment method
     *
     * @param array $paymentCode
     * @return void
     */
    public function selectPaymentMethod(array $paymentCode)
    {
        $this->getBillingMethodBlock()->selectPaymentMethod($paymentCode);
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Submit order
     *
     * @return void
     */
    public function submitOrder()
    {
        $this->getTotalsBlock()->submitOrder();
    }

    /**
     * Click "Add Selected Product(s) to Order" button
     *
     * @return void
     */
    public function addSelectedProductsToOrder()
    {
        $this->_rootElement->find($this->addSelectedProducts)->click();
    }
}
