<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model;

use Magento\Framework\View\Element\UiComponent\ContextFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Dynamically generate UI Component
 *
 * Sometimes we need to generate components dynamically (not from layout).
 * The basic example, is creating widget UI component, based on CMS page or CMS block
 * directive
 */
class UiComponentGenerator
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * UiComponentGenerator constructor.
     * @param ContextFactory $contextFactory
     * @param UiComponentFactory $uiComponentFactory
     * @param array $data
     */
    public function __construct(
        ContextFactory $contextFactory,
        UiComponentFactory $uiComponentFactory
    ) {
        $this->contextFactory = $contextFactory;
        $this->uiComponentFactory = $uiComponentFactory;
    }

    /**
     * Allows to generate Ui component
     *
     * @param string $name
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @return UiComponentInterface
     */
    public function generateUiComponent($name, \Magento\Framework\View\LayoutInterface $layout)
    {
        $context = $this->contextFactory->create([
            'namespace' => $name,
            'pageLayout' => $layout,
        ]);

        $component = $this->uiComponentFactory->create(
            $name,
            null,
            [
                'context' => $context
            ]
        );
        return $this->prepareComponent($component);
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return UiComponentInterface
     */
    private function prepareComponent(UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareComponent($child);
            }
        }
        $component->prepare();

        return $component;
    }
}
