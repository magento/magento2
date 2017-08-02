<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * @api
 * @since 2.0.0
 */
class MassAction extends AbstractComponent
{
    const NAME = 'massaction';

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function prepare()
    {
        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $config['actions'][] = $actionComponent->getConfiguration();
        }

        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }

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
