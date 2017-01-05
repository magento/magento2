<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Adminhtml;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractForm;
use Magento\Shipping\Test\Block\Adminhtml\Form\Items;
use Magento\Shipping\Test\Block\Adminhtml\Order\Tracking;

/**
 * Shipment create form.
 */
class Form extends AbstractForm
{
    /**
     * Items block css selector.
     *
     * @var string
     */
    protected $items = '#ship_items_container';

    /**
     * Tracking block css selector.
     *
     * @var string
     */
    protected $tracking = '#tracking_numbers_table';

    /**
     * Get items block.
     *
     * @return Items
     */
    protected function getItemsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Shipping\Test\Block\Adminhtml\Form\Items::class,
            ['element' => $this->_rootElement->find($this->items)]
        );
    }

    /**
     * Get tracking block.
     *
     * @return Tracking
     */
    protected function getTrackingBlock()
    {
        return $this->blockFactory->create(
            \Magento\Shipping\Test\Block\Adminhtml\Order\Tracking::class,
            ['element' => $this->_rootElement->find($this->tracking)]
        );
    }

    /**
     * Fill form data.
     *
     * @param array $data
     * @param array|null $products [optional]
     * @return void
     */
    public function fillData(array $data, $products = null)
    {
        $data = $this->prepareData($data);
        if (isset($data['form_data'])) {
            if (isset($data['form_data']['tracking'])) {
                $this->getTrackingBlock()->fill($data['form_data']['tracking']);
                unset($data['form_data']['tracking']);
            }
            $this->_fill($this->dataMapping($data['form_data']));
        }
        if (isset($data['items_data']) && $products !== null) {
            foreach ($products as $key => $product) {
                $this->getItemsBlock()->getItemProductBlock($product)->fillProduct($data['items_data'][$key]);
            }
        }
    }
}
