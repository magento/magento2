<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 2.0.0
 */
class Range extends AbstractElement
{
    const NAME = 'range';

    /**
     * Get component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->getData('input_type');
    }
}
