<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 100.0.2
 */
class MultiSelect extends AbstractOptionsField
{
    const NAME = 'multiselect';

    const DEFAULT_SIZE = 6;

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config['size'] = self::DEFAULT_SIZE;
        $this->setData('config', array_replace_recursive((array)$this->getData('config'), $config));
        parent::prepare();
    }

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
