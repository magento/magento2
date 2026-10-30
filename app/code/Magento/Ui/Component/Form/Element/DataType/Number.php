<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * @api
 * @since 100.0.2
 */
class Number extends AbstractDataType
{
    const NAME = 'number';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
