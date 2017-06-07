<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Items;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Item product block.
 */
class ItemProduct extends Form
{
    /**
     * Actions for fields.
     *
     * @var array
     */
    protected $actions = [
        'name' => 'getText',
        'price' => 'getText',
        'qty' => 'getValue',
        'checkout_data' => 'getValue',
    ];

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '//ancestor::body/div[@id="loading-mask"]';

    /**
     * Configure button locator.
     *
     * @var string
     */
    protected $configureButton = 'button';

    /**
     * Order items block locator.
     *
     * @var string
     */
    protected $orderItemsBlock = '#order-items .title';

    /**
     * Get data item products.
     *
     * @param array $fields
     * @param string $currency [optional]
     * @return array
     */
    public function getCheckoutData(array $fields, $currency = '$')
    {
        $result = [];
        $data = $this->dataMapping($fields);
        foreach ($data as $key => $item) {
            if (!isset($item['value'])) {
                $result[$key] = $this->getCheckoutData($item);
                continue;
            }
            $value = $this->_rootElement->find(
                $item['selector'],
                $item['strategy'],
                $item['input']
            )->{$this->actions[$key]}();

            $result[$key] = str_replace($currency, '', trim($value));
        }

        return $result;
    }

    /**
     * Fill the root form with checkout data.
     *
     * @param array $data
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillCheckoutData(array $data, SimpleElement $element = null)
    {
        if (isset($data['cartItem'])) {
            unset($data['cartItem']);
        }
        if (isset($data['options'])) {
            unset($data['options']);
        }
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Click Configure button.
     *
     * @return void
     */
    public function configure()
    {
        $this->browser->find($this->orderItemsBlock)->click();
        $this->_rootElement->find($this->configureButton)->click();
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
    }
}
