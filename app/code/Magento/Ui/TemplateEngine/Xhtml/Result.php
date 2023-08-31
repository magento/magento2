<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml;

use DOMElement;
use Magento\Framework\App\State;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Layout\Generator\Structure;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\ResultInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Convert DOMElement to string representation
 */
class Result implements ResultInterface
{
    /**
     * @param Template $template
     * @param CompilerInterface $compiler
     * @param UiComponentInterface $component
     * @param Structure $structure
     * @param LoggerInterface $logger
     * @param JsonHexTag $jsonSerializer
     * @param State $state
     */
    public function __construct(
        protected readonly Template $template,
        protected readonly CompilerInterface $compiler,
        protected readonly UiComponentInterface $component,
        protected readonly Structure $structure,
        protected readonly LoggerInterface $logger,
        private readonly JsonHexTag $jsonSerializer,
        private readonly State $state
    ) {
    }

    /**
     * Get result document root element \DOMElement
     *
     * @return DOMElement
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
        $layoutConfiguration = $this->wrapContent(
            $this->jsonSerializer->serialize($this->structure->generate($this->component))
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
        } catch (Throwable $e) {
            $this->logger->critical($e);
            $result = $e->getMessage();
            if ($this->state->getMode() === State::MODE_DEVELOPER) {
                $result .= "<pre><code>Exception in {$e->getFile()}:{$e->getLine()}</code></pre>";
            }
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
        . '{"*": {"Magento_Ui/js/core/app": ' . str_replace(['<![CDATA[', ']]>'], '', $content) . '}}'
        . ']]></script>';
    }
}
