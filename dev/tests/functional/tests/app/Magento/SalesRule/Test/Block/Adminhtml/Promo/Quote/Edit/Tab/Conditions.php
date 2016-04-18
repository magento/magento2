<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Sales rule condition tab.
 */
class Conditions extends Tab
{
    /**
     * Field Prefix Constant
     */
    const FIELD_PREFIX = '#conditions__1__';

    /**
     * Set the mapping and fill the form.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        foreach ($fields as $key => $value) {
            $this->mapping[$key] = self::FIELD_PREFIX . $key;
        }
        return parent::fillFormTab($fields, $element);
    }
}
