<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml;

use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Layout\LayoutPool;

/**
 * Class Result
 */
class Result
{
    /**
     * @var Template
     */
    protected $template;

    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * @var UiComponentInterface
     */
    protected $component;

    /**
     * @var LayoutPool
     */
    protected $layoutPool;

    /**
     * Constructor
     *
     * @param Template $template
     * @param Compiler $compiler
     * @param UiComponentInterface $component
     * @param LayoutPool $layoutPool
     */
    public function __construct(
        Template $template,
        Compiler $compiler,
        UiComponentInterface $component,
        LayoutPool $layoutPool
    ) {
        $this->template = $template;
        $this->compiler = $compiler;
        $this->component = $component;
        $this->layoutPool = $layoutPool;
    }

    /**
     * Get result document root element \DOMElement
     *
     * @return \DOMElement
     */
    public function getDocumentElement()
    {
        return $this->template->getDocumentElement();
    }

    /**
     * Append layout configuration
     *
     * @return void
     */
    public function appendLayoutConfiguration()
    {
        /** @var LayoutInterface $layout */
        if (!$layoutDefinition = $this->component->getData('layout')) {
            $layoutDefinition = [
                'type' => 'generic'
            ];
        }

        $layout = $this->layoutPool->create($layoutDefinition['type'], $layoutDefinition);
        $layoutConfiguration = $this->wrapContent(
            json_encode($layout->build($this->component))
        );
        $this->template->append($layoutConfiguration);
    }

    /**
     * Returns the string representation
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $templateRootElement = $this->getDocumentElement();
            $this->compiler->compile($templateRootElement, $this->component, $this->component);
            $this->appendLayoutConfiguration();
            $result = $this->compiler->postprocessing($this->template->__toString());
        } catch (\Exception $e) {
            $result = '';
        }
        return $result;
    }

    /**
     * Wrap content
     *
     * @param string $content
     * @return string
     */
    protected function wrapContent($content)
    {
        return '<script type="text/x-magento-init"><![CDATA['
        . '{"*": {"Magento_Ui/js/core/app": ' . $content . '}}'
        . ']]></script>';
    }
}
