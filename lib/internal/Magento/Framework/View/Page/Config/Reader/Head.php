<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Page\Config\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Head structure reader is intended for collecting assets, title and metadata
 */
class Head implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_HEAD = 'head';
    /**#@-*/

    /**#@+
     * Supported head elements
     */
    const HEAD_CSS = 'css';
    const HEAD_SCRIPT = 'script';
    const HEAD_LINK = 'link';
    const HEAD_REMOVE = 'remove';
    const HEAD_TITLE = 'title';
    const HEAD_META = 'meta';
    const HEAD_ATTRIBUTE = 'attribute';
    /**#@-*/

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_HEAD];
    }

    /**
     * {@inheritdoc}
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Element $headElement
     * @return $this
     */
    public function interpret(
        Layout\Reader\Context $readerContext,
        Layout\Element $headElement
    ) {
        $pageConfigStructure = $readerContext->getPageConfigStructure();
        /** @var \Magento\Framework\View\Layout\Element $node */
        foreach ($headElement as $node) {
            switch ($node->getName()) {
                case self::HEAD_CSS:
                case self::HEAD_SCRIPT:
                case self::HEAD_LINK:
                    $pageConfigStructure->addAssets($node->getAttribute('src'), $this->getAttributes($node));
                    break;

                case self::HEAD_REMOVE:
                    $pageConfigStructure->removeAssets($node->getAttribute('src'));
                    break;

                case self::HEAD_TITLE:
                    $pageConfigStructure->setTitle($node);
                    break;

                case self::HEAD_META:
                    $pageConfigStructure->setMetaData($node->getAttribute('name'), $node->getAttribute('content'));
                    break;

                case self::HEAD_ATTRIBUTE:
                    $pageConfigStructure->setElementAttribute(
                        PageConfig::ELEMENT_TYPE_HEAD,
                        $node->getAttribute('name'),
                        $node->getAttribute('value')
                    );
                    break;

                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * Get all attributes for current dom element
     *
     * @param \Magento\Framework\View\Layout\Element $element
     * @return array
     */
    protected function getAttributes($element)
    {
        $attributes = [];
        foreach ($element->attributes() as $attrName => $attrValue) {
            $attributes[$attrName] = (string)$attrValue;
        }
        return $attributes;
    }
}
