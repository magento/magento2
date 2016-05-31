<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Class Element
 */
class Element extends \Magento\Framework\Simplexml\Element
{
    /**#@+
     * Supported layout directives
     */
    const TYPE_RENDERER = 'renderer';

    const TYPE_TEMPLATE = 'template';

    const TYPE_DATA = 'data';

    const TYPE_BLOCK = 'block';

    const TYPE_CONTAINER = 'container';

    const TYPE_ACTION = 'action';

    const TYPE_ARGUMENTS = 'arguments';

    const TYPE_ARGUMENT = 'argument';

    const TYPE_REFERENCE_BLOCK = 'referenceBlock';

    const TYPE_REFERENCE_CONTAINER = 'referenceContainer';

    const TYPE_REMOVE = 'remove';

    const TYPE_MOVE = 'move';

    const TYPE_UI_COMPONENT = 'uiComponent';

    const TYPE_HEAD = 'head';

    /**#@-*/

    /**#@+
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG = 'htmlTag';

    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';

    const CONTAINER_OPT_HTML_ID = 'htmlId';

    const CONTAINER_OPT_LABEL = 'label';

    /**#@-*/

    /**
     * Prepare the element
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepare()
    {
        switch ($this->getName()) {
            case self::TYPE_BLOCK:
            case self::TYPE_RENDERER:
            case self::TYPE_TEMPLATE:
            case self::TYPE_DATA:
            case self::TYPE_UI_COMPONENT:
                $this->prepareBlock();
                break;
            case self::TYPE_REFERENCE_BLOCK:
            case self::TYPE_REFERENCE_CONTAINER:
                $this->prepareReference();
                break;
            case self::TYPE_ACTION:
                $this->prepareAction();
                break;
            case self::TYPE_ARGUMENT:
                $this->prepareActionArgument();
                break;
            default:
                break;
        }
        foreach ($this as $child) {
            /** @var Element $child */
            $child->prepare();
        }
        return $this;
    }

    /**
     * Get block name
     *
     * @return bool|string
     */
    public function getBlockName()
    {
        $tagName = (string)$this->getName();
        $isThisBlock = empty($this['name']) || !in_array(
            $tagName,
            [self::TYPE_BLOCK, self::TYPE_REFERENCE_BLOCK]
        );

        if ($isThisBlock) {
            return false;
        }
        return (string)$this['name'];
    }

    /**
     * Get element name
     *
     * Advanced version of getBlockName() method: gets name for container as well as for block
     *
     * @return string|bool
     */
    public function getElementName()
    {
        $tagName = $this->getName();
        $isThisContainer = !in_array(
            $tagName,
            [self::TYPE_BLOCK, self::TYPE_REFERENCE_BLOCK, self::TYPE_CONTAINER, self::TYPE_REFERENCE_CONTAINER]
        );

        if ($isThisContainer) {
            return false;
        }
        return $this->getAttribute('name');
    }

    /**
     * Extracts sibling from 'before' and 'after' attributes
     *
     * @return string
     */
    public function getSibling()
    {
        $sibling = null;
        if ($this->getAttribute('before')) {
            $sibling = $this->getAttribute('before');
        } elseif ($this->getAttribute('after')) {
            $sibling = $this->getAttribute('after');
        }

        return $sibling;
    }

    /**
     * Add parent element name to parent attribute
     *
     * @return $this
     */
    public function prepareBlock()
    {
        $parent = $this->getParent();
        if (isset($parent['name']) && !isset($this['parent'])) {
            $this->addAttribute('parent', (string)$parent['name']);
        }

        return $this;
    }

    /**
     * Prepare references
     *
     * @return $this
     */
    public function prepareReference()
    {
        return $this;
    }

    /**
     * Add parent element name to block attribute
     *
     * @return $this
     */
    public function prepareAction()
    {
        $parent = $this->getParent();
        $this->addAttribute('block', (string)$parent['name']);

        return $this;
    }

    /**
     * Prepare action argument
     *
     * @return $this
     */
    public function prepareActionArgument()
    {
        return $this;
    }

    /**
     * Returns information is this element allows caching
     *
     * @return bool
     */
    public function isCacheable()
    {
        return !(bool)count($this->xpath('//' . self::TYPE_BLOCK . '[@cacheable="false"]'));
    }
}
