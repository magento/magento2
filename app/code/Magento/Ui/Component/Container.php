<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * @api
 * @since 100.0.2
 */
class Container extends AbstractComponent
{
    const NAME = 'container';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        $type = $this->getData('type');
        return static::NAME . ($type ? ('.' . $type): '');
    }
}
