<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class Group
 */
class Group extends AbstractComponent
{
    const NAME = 'group';

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
     * @return string
     */
    public function getIsRequired()
    {
        return $this->getData('required') ? 'required' : '';
    }
}
