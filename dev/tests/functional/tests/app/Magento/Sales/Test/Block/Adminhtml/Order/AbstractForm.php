<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Form;

/**
 * Abstract Form block.
 */
abstract class AbstractForm extends Form
{
    /**
     * Send button css selector.
     *
     * @var string
     */
    protected $send = '[data-ui-id="order-items-submit-button"]';

    /**
     * Loader css selector.
     *
     * @var string
     */
    protected $loader = '[data-role="loader"]';

    /**
     * Wait loader to disappear.
     *
     * @return void
     */
    protected function waitLoader()
    {
        $this->browser->waitUntil(
            function () {
                $element = $this->browser->find($this->loader);
                return $element->isVisible() == false ? true : null;
            }
        );
    }

    /**
     * Fill form data.
     *
     * @param array $data
     * @return void
     */
    public function fillFormData(array $data)
    {
        $data = $this->prepareData($data);
        if (isset($data['form_data'])) {
            $data['form_data'] = $this->dataMapping($data['form_data']);
            $this->_fill($data['form_data']);
        }
    }

    /**
     * Fill product data.
     *
     * @param array $data
     * @param array|null $products [optional]
     * @return void
     */
    public function fillProductData(array $data, $products = null)
    {
        $data = $this->prepareData($data);
        if (isset($data['items_data']) && $products !== null) {
            foreach ($data['items_data'] as $key => $item) {
                $productSku = is_array($products[$key]) ?  $products[$key]['sku'] : $products[$key]->getData()['sku'];
                $this->getItemsBlock()->getItemProductBlock($productSku)->fillProduct($item);
            }
        }
    }

    /**
     * Fill form and product data.
     *
     * @param array $data
     * @param array|null $products [optional]
     * @return void
     */
    public function fillData(array $data, $products = null)
    {
        $this->fillFormData($data);
        $this->fillProductData($data, $products);
    }

    /**
     * Click update qty's button.
     *
     * @return void
     */
    public function updateQty()
    {
        $this->getItemsBlock()->clickUpdateQty();
        $this->waitLoader();
    }

    /**
     * Get items block.
     *
     * @return AbstractItemsNewBlock
     */
    abstract protected function getItemsBlock();

    /**
     * Submit order.
     *
     * @return void
     */
    public function submit()
    {
        $this->waitLoader();

        $this->_rootElement->find($this->send)->click();
    }

    /**
     * Prepare data.
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
