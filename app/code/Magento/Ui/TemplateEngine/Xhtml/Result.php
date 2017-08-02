<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml;

use Magento\Framework\View\Layout\Generator\Structure;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Framework\View\TemplateEngine\Xhtml\ResultInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Result
 * @since 2.0.0
 */
class Result implements ResultInterface
{
    /**
     * @var Template
     * @since 2.0.0
     */
    protected $template;

    /**
     * @var CompilerInterface
     * @since 2.0.0
     */
    protected $compiler;

    /**
     * @var UiComponentInterface
     * @since 2.0.0
     */
    protected $component;

    /**
     * @var Structure
     * @since 2.0.0
     */
    protected $structure;

    /**
     * @var LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @param Template $template
     * @param CompilerInterface $compiler
     * @param UiComponentInterface $component
     * @param Structure $structure
     * @param LoggerInterface $logger
     * @since 2.0.0
     */
    public function __construct(
        Template $template,
        CompilerInterface $compiler,
        UiComponentInterface $component,
        Structure $structure,
        LoggerInterface $logger
    ) {
        $this->template = $template;
        $this->compiler = $compiler;
        $this->component = $component;
        $this->structure = $structure;
        $this->logger = $logger;
    }

    /**
     * Get result document root element \DOMElement
     *
     * @return \DOMElement
     * @since 2.0.0
     */
    public function getDocumentElement()
    {
        return $this->template->getDocumentElement();
    }

    /**
     * Append layout configuration
     *
     * @return void
     * @since 2.0.0
     */
    public function appendLayoutConfiguration()
    {
        $layoutConfiguration = $this->wrapContent(json_encode($this->structure->generate($this->component)));
        $this->template->append($layoutConfiguration);
    }

    /**
     * Returns the string representation
     *
     * @return string
     * @since 2.0.0
     */
    public function __toString()
    {
        try {
            $templateRootElement = $this->getDocumentElement();
            foreach ($templateRootElement->attributes as $name => $attribute) {
                if ('noNamespaceSchemaLocation' === $name) {
                    $this->getDocumentElement()->removeAttributeNode($attribute);
                    break;
                }
            }
            $templateRootElement->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi');
            $this->compiler->compile($templateRootElement, $this->component, $this->component);
            $this->appendLayoutConfiguration();
            $result = $this->compiler->postprocessing($this->template->__toString());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = $e->getMessage();
        }
        return $result;
    }

    /**
     * Wrap content
     *
     * @param string $content
     * @return string
     * @since 2.0.0
     */
    protected function wrapContent($content)
    {
        return '<script type="text/x-magento-init"><![CDATA['
        . '{"*": {"Magento_Ui/js/core/app": ' . str_replace(['<![CDATA[', ']]>'], '', $content) . '}}'
        . ']]></script>';
    }
}
