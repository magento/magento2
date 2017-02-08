<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * Class RadioSet
 */
class RadioSet extends AbstractOptionsField
{
    const NAME = 'radioset';

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
        return $this->getValue() == $optionValue;
    }
}
