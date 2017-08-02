<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml;

use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\AttributeInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\CdataInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\CommentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element\ElementInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\TextInterface;

/**
 * Class Compiler
 * @since 2.0.0
 */
class Compiler implements CompilerInterface
{
    /**
     * @var TextInterface
     * @since 2.0.0
     */
    protected $compilerText;

    /**
     * @var AttributeInterface
     * @since 2.0.0
     */
    protected $compilerAttribute;

    /**
     * @var CdataInterface
     * @since 2.0.0
     */
    protected $compilerCdata;

    /**
     * @var CommentInterface
     * @since 2.0.0
     */
    protected $compilerComment;

    /**
     * @var ElementInterface[]
     * @since 2.0.0
     */
    protected $elementCompilers;

    /**
     * Postprocessing data
     *
     * @var array
     * @since 2.0.0
     */
    protected $data;

    /**
     * Constructor
     *
     * @param TextInterface $compilerText
     * @param AttributeInterface $compilerAttribute
     * @param AttributeInterface|CdataInterface $compilerCdata
     * @param CommentInterface $compilerComment
     * @param ElementInterface[] $elementCompilers
     * @since 2.0.0
     */
    public function __construct(
        TextInterface $compilerText,
        AttributeInterface $compilerAttribute,
        CdataInterface $compilerCdata,
        CommentInterface $compilerComment,
        array $elementCompilers
    ) {
        $this->compilerText = $compilerText;
        $this->compilerAttribute = $compilerAttribute;
        $this->compilerCdata = $compilerCdata;
        $this->compilerComment = $compilerComment;
        $this->elementCompilers = $elementCompilers;
    }

    /**
     * The compilation of the template and filling in the data
     *
     * @param \DOMNode $node
     * @param DataObject $processedObject
     * @param DataObject $context
     * @return void
     * @since 2.0.0
     */
    public function compile(\DOMNode $node, DataObject $processedObject, DataObject $context)
    {
        switch ($node->nodeType) {
            case XML_TEXT_NODE:
                $this->compilerText->compile($node, $processedObject);
                break;
            case XML_CDATA_SECTION_NODE:
                $this->compilerCdata->compile($node, $processedObject);
                break;
            case XML_COMMENT_NODE:
                $this->compilerComment->compile($node, $processedObject);
                break;
            default:
                /** @var \DomElement $node */
                if ($node->hasAttributes()) {
                    foreach ($node->attributes as $attribute) {
                        $this->compilerAttribute->compile($attribute, $processedObject);
                    }
                }
                $compiler = $this->getElementCompiler($node->nodeName);
                if (null !== $compiler) {
                    $compiler->compile($this, $node, $processedObject, $context);
                } elseif ($node->hasChildNodes()) {
                    foreach ($this->getChildNodes($node) as $child) {
                        $this->compile($child, $processedObject, $context);
                    }
                }
        }
    }

    /**
     * Run postprocessing contents
     *
     * @param string $content
     * @return string
     * @since 2.0.0
     */
    public function postprocessing($content)
    {
        $patternTag = preg_quote(CompilerInterface::PATTERN_TAG);
        return preg_replace_callback(
            '#' . $patternTag . '(.+?)' . $patternTag . '#',
            function ($match) {
                return isset($this->data[$match[1]]) ? $this->data[$match[1]] : '';
            },
            $content
        );
    }

    /**
     * Set postprocessing data
     *
     * @param string $key
     * @param string $content
     * @return void
     * @since 2.0.0
     */
    public function setPostprocessingData($key, $content)
    {
        $this->data[$key] = $content;
    }

    /**
     * Get child nodes
     *
     * @param \DOMElement $node
     * @return \DOMElement[]
     * @since 2.0.0
     */
    protected function getChildNodes(\DOMElement $node)
    {
        $childNodes = [];
        foreach ($node->childNodes as $child) {
            $childNodes[] = $child;
        }

        return $childNodes;
    }

    /**
     * Get element compiler by name
     *
     * @param string $name
     * @return ElementInterface
     * @since 2.0.0
     */
    protected function getElementCompiler($name)
    {
        if (isset($this->elementCompilers[$name])) {
            return $this->elementCompilers[$name];
        }

        return null;
    }
}
