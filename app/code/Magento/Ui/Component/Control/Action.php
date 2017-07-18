<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;

/**
 * Class Action
 */
class Action extends AbstractComponent implements ControlInterface
{
    const NAME = 'action';

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
