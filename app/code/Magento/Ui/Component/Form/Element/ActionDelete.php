<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 */
class ActionDelete extends AbstractElement
{
    const NAME = 'actionDelete';

    /**
     * {@inheritdoc}
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
