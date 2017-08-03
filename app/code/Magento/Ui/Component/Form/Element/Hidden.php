<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 2.1.0
 */
class Hidden extends AbstractElement
{
    const NAME = 'hidden';

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
