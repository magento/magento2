<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

use Magento\Ui\Component\AbstractComponent;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;

/**
 * @api
 * @since 2.0.0
 */
class Columns extends AbstractComponent
{
    const NAME = 'columns';

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
