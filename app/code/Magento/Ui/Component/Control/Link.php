<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Control;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class Link
 */
class Link extends AbstractComponent
{
    const NAME = 'link';

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
