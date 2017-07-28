<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends AbstractComponent implements UiComponentInterface
{
    const NAME = 'collection';

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
}
