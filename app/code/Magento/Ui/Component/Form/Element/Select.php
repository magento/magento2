<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * Class Select
 */
class Select extends AbstractOptionsField
{
    const NAME = 'select';

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
     * {@inheritdoc}
     */
    public function getIsSelected($optionValue)
    {
        return $this->getValue() == $optionValue;
    }
}
