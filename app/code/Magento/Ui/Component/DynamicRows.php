<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * Ui component DynamicRows
 * @api
 * @since 2.1.0
 */
class DynamicRows extends AbstractComponent
{
    const NAME = 'dynamicRows';

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
