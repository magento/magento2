<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 100.0.2
 */
class Radio extends AbstractElement
{
    const NAME = 'radio';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getChecked()
    {
        return false;
    }
}
