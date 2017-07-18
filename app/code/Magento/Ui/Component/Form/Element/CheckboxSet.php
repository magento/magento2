<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 */
class CheckboxSet extends AbstractOptionsField
{
    const NAME = 'checkboxset';

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSelected($optionValue)
    {
        return in_array($optionValue, (array) $this->getValue());
    }
}
