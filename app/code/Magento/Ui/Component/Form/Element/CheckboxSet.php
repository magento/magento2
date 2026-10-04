<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 100.1.0
 */
class CheckboxSet extends AbstractOptionsField
{
    const NAME = 'checkboxset';

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getIsSelected($optionValue)
    {
        return in_array($optionValue, (array) $this->getValue());
    }
}
