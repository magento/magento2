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
 * Dynamicly generate UI Component
 *
 * Sometimes we need to generate components dynamicly (not from layout).
 * Tha basic example, is creating widget UI component, based on CMS page or CMS block
 * directive
 * @since 2.2.0
 */
class UiComponentGenerator
{
    /**
     * @var ContextFactory
     * @since 2.2.0
     */
    private $contextFactory;

    /**
     * @var UiComponentFactory
     * @since 2.2.0
     */
    private $uiComponentFactory;

    /**
     * UiComponentGenerator constructor.
     * @param ContextFactory $contextFactory
     * @param UiComponentFactory $uiComponentFactory
     * @param array $data
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
