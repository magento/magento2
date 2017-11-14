<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Element\SelectElement;
use Magento\Mtf\Client\Locator;

/**
 * Edit Shipping Address Form.
 */
class Address extends Block
{
    /**
     * Fields to collect from.
     *
     * @var array
     */
    private static $fields = [
        'firstname',
        'lastname',
        'company',
        'telephone',
        'street_1',
        'street_2',
        'city',
        'region_id',
        'zip',
        'country'
    ];

    /**
     * Collect address data.
     *
     * @return array
     */
    public function getData()
    {
        $data = [];
        foreach (self::$fields as $field) {
            if ($field == 'region_id' || $field == 'country') {
                /** @var SelectElement $element */
                $element = $this->_rootElement->find('#' . $field, Locator::SELECTOR_CSS, 'select');
                $data[$field] = $element->getValue();
            } else {
                $data[$field] = $this->_rootElement->find('#' . $field)->getValue();
            }
        }
        $data['street'] = rtrim($data['street_1'] . ' ' . $data['street_2']);
        $data['country_id'] = $data['country'];
        $data['postcode'] = $data['zip'];
        unset($data['street_1']);
        unset($data['street_2']);
        unset($data['country']);
        unset($data['zip']);

        return $data;
    }
}
