<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Mtf\Block\Form;

/**
 * Class Form
 * Abstract Form block
 */
abstract class AbstractForm extends Form
{
    /**
     * Send button css selector
     *
     * @var string
     */
    protected $send = '[data-ui-id="order-items-submit-button"]';

    /**
     * Loader css selector
     *
     * @var string
     */
    protected $loader = '[data-role="loader"]';

    /**
     * Fill form data
     *
     * @param array $data
     * @param array|null $products [optional]
     * @return void
     */
    public function fillData(array $data, $products = null)
    {
        $data = $this->prepareData($data);
        if (isset($data['form_data'])) {
            $data['form_data'] = $this->dataMapping($data['form_data']);
            $this->_fill($data['form_data']);
        }
        if (isset($data['items_data']) && $products !== null) {
            foreach ($products as $key => $product) {
                $this->getItemsBlock()->getItemProductBlock($product)->fillProduct($data['items_data'][$key]);
            }
        }
    }

    /**
     * Click update qty's button
     *
     * @return void
     */
    public function updateQty()
    {
        $this->getItemsBlock()->clickUpdateQty();
    }

    /**
     * Get items block
     *
     * @return AbstractItemsNewBlock
     */
    abstract protected function getItemsBlock();

    /**
     * Submit order
     *
     * @return void
     */
    public function submit()
    {
        $browser = $this->browser;
        $selector = $this->loader;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
        $this->reinitRootElement();
        $this->_rootElement->find($this->send)->click();
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return array|null
     */
    protected function prepareData(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->prepareData($value);
            }
            if ($value !== '-' && $value !== null) {
                $result[$key] = $value;
            }
        }

        return empty($result) ? null : $result;
    }
}
