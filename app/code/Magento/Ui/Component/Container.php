<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * Class Container
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
        return static::NAME . (!empty($type) ? '.' . $type : '');
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}
