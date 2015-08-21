<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MassAction
 */
class MassAction extends AbstractComponent
{
    const NAME = 'massaction';

    /**
     * Default component data
     *
     * @var array
     */
    protected $_data = [
        'config' => [
            'actions' => []
        ]
    ];

    /**
     * @var OptionSourceInterface
     */
    protected $optionsProvider;

    /**
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param OptionSourceInterface|null $optionsProvider
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = [],
        OptionSourceInterface $optionsProvider = null
    ) {
        parent::__construct($context, $components, $data);
        $this->optionsProvider = $optionsProvider;
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

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        $actionOptions = ($this->optionsProvider) ? $this->optionsProvider->toOptionArray() : [];
        $config = $this->getData('config');
        if (isset($config['actions'])) {
            /* Process dynamic actions */
            foreach ($config['actions'] as $key => &$data) {
                $additionalActions = isset($actionOptions[$key]) ? $actionOptions[$key] : [];
                $removeParent = false;
                foreach ($additionalActions as $additionalAction) {
                    $additionalAction['url'] = $this->getContext()->getUrl($data['url'], $additionalAction['url']);
                    $removeParent = true;
                    $data['actions'][] = $additionalAction;
                }
                if ($removeParent) {
                    unset($data['url']);
                }
            }
            $config['actions'] = array_values($config['actions']);
            array_walk_recursive(
                $config['actions'],
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

        parent::prepare();
    }
}
