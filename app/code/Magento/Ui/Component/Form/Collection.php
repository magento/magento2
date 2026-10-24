<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractComponent implements UiComponentInterface
{
    const NAME = 'collection';

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
