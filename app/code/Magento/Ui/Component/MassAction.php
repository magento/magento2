<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class MassAction
 */
class MassAction extends AbstractComponent
{
    const NAME = 'massaction';

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
     * Prepare component data
     *
     * @return void
     */
    public function prepare()
    {
        $this->prepareConfiguration();
        $config = $this->getData('config');
        if (isset($config['actions'])) {
            array_walk_recursive(
                $config,
                function (&$item, $key, $context) {
                    /** @var ContextInterface $context */
                    if ($key === 'url') {
                        $item = $context->getUrl($item);
                    }
                },
                $this->getContext()
            );
            $this->setData('config', $config);
        }

        $jsConfig = $this->getJsConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return ['actions' => []];
    }
}
