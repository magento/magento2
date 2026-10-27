<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component;

/**
 * Ui component DynamicRows
 * @api
 * @since 100.1.0
 */
class DynamicRows extends AbstractComponent
{
    const NAME = 'dynamicRows';

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
