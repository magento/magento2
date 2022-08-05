<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * Mass action UI component.
 *
 * @api
 * @since 100.0.2
 */
class MassAction extends AbstractComponent
{
    const NAME = 'massaction';

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $componentConfig = $actionComponent->getConfiguration();
            $disabledAction = $componentConfig['actionDisable'] ?? false;
            if ($disabledAction) {
                continue;
            }
            $config['actions'][] = $componentConfig;
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
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
