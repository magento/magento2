<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Object;
use Magento\Framework\View\Element\UiComponent\Context as RenderContext;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;

/**
 * Class UiComponentFactory
 */
class UiComponentFactory extends Object
{
    /**
     * Ui element view
     *
     * @var UiComponentInterface
     */
    protected $view;

    /**
     * Render context
     *
     * @var RenderContext
     */
    protected $renderContext;

    /**
     * Layout Interface
     *
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var bool
     */
    protected $layoutLoaded = false;

    /**
     * Constructor
     *
     * @param RenderContext $renderContext
     * @param LayoutFactory $layoutFactory
     * @param array $data
     */
    public function __construct(
        RenderContext $renderContext,
        LayoutFactory $layoutFactory,
        array $data = []
    ) {
        $this->renderContext = $renderContext;
        $this->renderContext->setRender($this);
        $this->layoutFactory = $layoutFactory;
        parent::__construct($data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponent()
    {
        return $this->getData('configuration/component');
    }

    /**
     * Get layout handle
     *
     * @return string
     */
    public function getLayoutHandle()
    {
        return $this->getData('configuration/name');
    }

    /**
     * @param LayoutInterface $layout
     * @return void
     */
    public function setLayout(LayoutInterface $layout)
    {
        if (!$this->renderContext->getPageLayout()) {
            $this->renderContext->setPageLayout($layout);
        }
    }

    /**
     * Create Ui Component instance
     *
     * @param string $componentName
     * @param string $handleName
     * @param array $arguments
     * @return UiComponentInterface
     */
    public function createUiComponent($componentName, $handleName, array $arguments = [])
    {
        if (!$this->layout) {
            $this->renderContext->setNamespace($handleName);
            $this->layout = $this->layoutFactory->create();
            $this->renderContext->setLayout($this->layout);
            $this->layout->getUpdate()->addHandle('ui_components');
            $this->layout->getUpdate()->addHandle($handleName);
            $this->loadLayout();
            $this->layoutLoaded = true;
        }

        $view = $this->getUiElementView($componentName);
        $view->update($arguments);
        if ($this->layoutLoaded) {
            $this->prepare($view);
        }

        return $view;
    }

    /**
     * Prepare UI Component data
     *
     * @param object $view
     * @return void
     */
    protected function prepare($view)
    {
        if ($view instanceof UiComponentInterface) {
            $view->prepare();
        }
        foreach ($view->getLayout()->getChildNames($view->getNameInLayout()) as $childAlias) {
            $name = $view->getLayout()->getChildName($view->getNameInLayout(), $childAlias);
            if ($view->getLayout()->isContainer($name)) {
                foreach ($view->getLayout()->getChildNames($name) as $childName) {
                    $child = $view->getLayout()->getBlock($childName);
                    $this->prepare($child);
                }
            } else {
                $child = $view->getChildBlock($childAlias);
                if ($child) {
                    $this->prepare($child);
                }
            }
        }
    }

    /**
     * Get UI Element View
     *
     * @param string $uiElementName
     * @return UiComponentInterface
     * @throws \InvalidArgumentException
     */
    public function getUiElementView($uiElementName)
    {
        /** @var UiComponentInterface $view */
        $view = $this->layout->getBlock($uiElementName);
        if (!$view instanceof UiComponentInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'UI Element "%s" must implement \Magento\Framework\View\Element\UiComponentInterface',
                    $uiElementName
                )
            );
        }
        return $view;
    }

    /**
     * Load layout
     *
     * @return void
     */
    protected function loadLayout()
    {
        $this->layout->getUpdate()->load();
        $this->layout->generateXml();
        $this->layout->generateElements();
    }
}
