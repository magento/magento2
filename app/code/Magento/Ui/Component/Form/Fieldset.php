<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\AbstractComponent;

/**
 * @api
 * @since 100.0.2
 */
class Fieldset extends AbstractComponent
{
    const NAME = 'fieldset';

    /**
     * @var bool
     */
    protected $collapsible = false;

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
     * Check that fieldset can be shown.
     *
     * @return bool
     */
    public function canShow(): bool
    {
        return true;
    }
}
