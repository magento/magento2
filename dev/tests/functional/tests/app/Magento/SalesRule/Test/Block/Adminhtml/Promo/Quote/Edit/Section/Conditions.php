<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Sales rule condition section.
 */
class Conditions extends Section
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
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        foreach ($fields as $key => $value) {
            $this->mapping[$key] = self::FIELD_PREFIX . $key;
        }
        return parent::setFieldsData($fields, $element);
    }
}
