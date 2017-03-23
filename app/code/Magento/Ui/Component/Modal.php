<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * Class Modal
 */
class Modal extends AbstractComponent
{
    const NAME = 'modal';

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
