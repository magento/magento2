<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;

/**
 * Class Link
 * @since 2.0.0
 */
class Link extends AbstractComponent implements ControlInterface
{
    const NAME = 'link';

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
